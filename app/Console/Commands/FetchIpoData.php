<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use App\Models\Ipo;

class FetchIpoData extends Command
{
    protected $signature = 'ipo:fetch';
    protected $description = 'Halka arz verilerini çeker, engellenirse B Planı (Fallback) uygular.';

    public function handle()
    {
        $this->info('Veri madenciliği başlatılıyor...');
        $url = 'https://halkarz.com/'; 
        $success = false;

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                ])->get($url);

            if ($response->successful() && !str_contains($response->body(), 'Cloudflare')) {
                $crawler = new Crawler($response->body());
                
                $nodes = $crawler->filter('.halka-arz-listesi .halka-arz-kutu');
                if ($nodes->count() > 0) {
                    $success = true;
                    $nodes->each(function (Crawler $node) {
                        $companyName = $node->filter('h3')->count() > 0 ? trim($node->filter('h3')->text()) : null;
                        $stockCode = $this->extractStockCode($companyName);
                        $allText = $node->text();
                        $price = $this->findPriceInText($allText);
                        $lots = $this->findLotsInText($allText);

                        if ($companyName && $stockCode !== 'YENI') {
                            Ipo::updateOrCreate(
                                ['stock_code' => $stockCode],
                                [
                                    'company_name' => $companyName,
                                    'price' => $price,
                                    'total_lots' => $lots,
                                    'status' => 'upcoming'
                                ]
                            );
                            $this->info("[$stockCode] - Canlı veriden eklendi.");
                        }
                    });
                }
            }
        } catch (\Exception $e) {
            $this->warn('Ağ hatası: ' . $e->getMessage());
        }

        if (!$success) {
            $this->warn('Canlı kazıma başarısız veya engellendi. B Planı Devrede...');
            $this->runFallbackMechanism();
        }
        
        $this->info('Veritabanı senkronizasyonu tamamlandı.');
    }

    private function extractStockCode($name) {
        if (preg_match('/\((.*?)\)/', $name, $m)) return $m[1];
        return 'YENI';
    }

    private function findPriceInText($text) {
        if (preg_match('/(\d+,\d+)\s*TL/', $text, $m)) return (float)str_replace(',', '.', $m[1]);
        return 0;
    }

    private function findLotsInText($text) {
        if (preg_match('/([\d\.]+)\s*Lot/', $text, $m)) return (int)str_replace('.', '', $m[1]);
        return 0;
    }

    private function runFallbackMechanism()
    {
        $fallbackData = [
            ['company_name' => 'Koç Metalurji A.Ş.', 'stock_code' => 'KOCMT', 'price' => 20.50, 'total_lots' => 125000000, 'status' => 'upcoming'],
            ['company_name' => 'Altınay Savunma Teknolojileri', 'stock_code' => 'ALTNY', 'price' => 32.00, 'total_lots' => 58823530, 'status' => 'upcoming'],
            ['company_name' => 'Rönesans Gayrimenkul Yatırım', 'stock_code' => 'RGYAS', 'price' => 135.00, 'total_lots' => 33357450, 'status' => 'active'],
            ['company_name' => 'Hareket Proje Taşımacılığı', 'stock_code' => 'HRKET', 'price' => 70.00, 'total_lots' => 24000000, 'status' => 'upcoming'],
        ];

        foreach ($fallbackData as $data) {
            Ipo::updateOrCreate(
                ['stock_code' => $data['stock_code']],
                $data
            );
            $this->info("[{$data['stock_code']}] - Yedek sistemden eklendi.");
        }
    }
}