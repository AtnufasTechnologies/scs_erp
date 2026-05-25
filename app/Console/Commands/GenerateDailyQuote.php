<?php

namespace App\Console\Commands;

use App\Models\Quote;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GenerateDailyQuote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate-daily-quote';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a daily quote';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $response = Http::get('https://favqs.com/api/qotd');
        if ($response->failed()) {
            $this->error('Failed to fetch quote: ' . $response->status());
            return;
        }
        $quote = $response->json();
        $quoteText = $quote['quote']['body'] ?? 'No quote available';
        $author = $quote['quote']['author'] ?? 'Unknown';
        Quote::create([
            'body' => $quoteText,
            'author' => $author,
            'is_active' => true,
        ]);
        $this->info('Daily quote generated successfully: "' . $quoteText . '" - ' . $author);
    }
}
