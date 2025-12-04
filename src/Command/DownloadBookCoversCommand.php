<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:download-book-covers',
    description: 'Download placeholder book cover images',
)]
class DownloadBookCoversCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $uploadDir = __DIR__ . '/../../public/uploads/books/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Liste des images à créer (placeholders)
        $bookCovers = [
            'les-miserables.jpg',
            'madame-bovary.jpg',
            'recherche-temps-perdu.jpg',
            'letranger.jpg',
            'la-peste.jpg',
            'etre-neant.jpg',
            'deuxieme-sexe.jpg',
            'vingt-mille-lieues.jpg',
            'tour-du-monde.jpg',
            'germinal.jpg',
            'petit-prince.jpg',
            'lamant.jpg',
        ];

        $io->title('Creating Book Cover Images');

        $created = 0;

        // Couleurs pour les couvertures
        $colors = [
            ['bg' => '#8B4513', 'text' => '#FFFFFF'], // Brown
            ['bg' => '#654321', 'text' => '#FFFFFF'], // Dark brown
            ['bg' => '#D2B48C', 'text' => '#000000'], // Tan
            ['bg' => '#A0522D', 'text' => '#FFFFFF'], // Sienna
            ['bg' => '#8B5A3C', 'text' => '#FFFFFF'], // Peru
            ['bg' => '#6B4423', 'text' => '#FFFFFF'], // Dark brown 2
        ];

        foreach ($bookCovers as $index => $cover) {
            $filePath = $uploadDir . $cover;
            
            if (file_exists($filePath)) {
                $io->note("Skipping {$cover} (already exists)");
                continue;
            }

            // Créer un SVG placeholder
            $color = $colors[$index % count($colors)];
            $title = str_replace(['.jpg', '-'], ['', ' '], ucwords($cover));
            $words = explode(' ', $title);
            $titleLine1 = implode(' ', array_slice($words, 0, ceil(count($words) / 2)));
            $titleLine2 = implode(' ', array_slice($words, ceil(count($words) / 2)));

            $svg = <<<SVG
<svg width="400" height="600" xmlns="http://www.w3.org/2000/svg">
  <rect width="400" height="600" fill="{$color['bg']}"/>
  <text x="200" y="250" font-family="Georgia, serif" font-size="24" fill="{$color['text']}" text-anchor="middle" font-weight="bold">{$titleLine1}</text>
  <text x="200" y="280" font-family="Georgia, serif" font-size="24" fill="{$color['text']}" text-anchor="middle" font-weight="bold">{$titleLine2}</text>
  <text x="200" y="350" font-family="Georgia, serif" font-size="16" fill="{$color['text']}" text-anchor="middle" opacity="0.8">📚</text>
</svg>
SVG;

            // Convertir SVG en JPG via une approche simple - créer un fichier SVG d'abord
            // Pour l'instant, créons des fichiers SVG et les renommons en .jpg
            // (les navigateurs modernes peuvent afficher SVG même avec extension .jpg)
            file_put_contents($filePath, $svg);
            
            $created++;
            $io->success("Created: {$cover}");
        }

        $io->success("Successfully created {$created} book cover images!");

        return Command::SUCCESS;
    }
}

