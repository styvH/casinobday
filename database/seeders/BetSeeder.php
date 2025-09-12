<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BetEvent;
use App\Models\BetChoice;

class BetSeeder extends Seeder
{
    public function run(): void
    {
        $datasets = [
            [
                'title' => 'Duel Poker Express',
                'status' => 'disponible',
                'description' => 'Heads-up rapide. Sélectionnez le vainqueur.',
                'margin' => 0.92,
                'choices' => [
                    ['code' => 'A', 'label' => 'Joueur A', 'participants_count' => 42],
                    ['code' => 'B', 'label' => 'Joueur B', 'participants_count' => 58],
                ],
            ],
            [
                'title' => 'Tournoi Mini 6 joueurs',
                'status' => 'annonce',
                'description' => 'Pariez sur le style de victoire.',
                'margin' => 0.90,
                'choices' => [
                    ['code' => 'ch', 'label' => 'Chip leader conserve', 'participants_count' => 20],
                    ['code' => 'up', 'label' => 'Remontada', 'participants_count' => 31],
                    ['code' => 'ko', 'label' => 'KO rapide final', 'participants_count' => 9],
                ],
            ],
            [
                'title' => 'Blackjack Série 5 mains',
                'status' => 'en_cours',
                'description' => 'Résultat cumul des 5 prochaines mains.',
                'margin' => 0.94,
                'choices' => [
                    ['code' => 'p', 'label' => 'Player domine', 'participants_count' => 70],
                    ['code' => 'd', 'label' => 'Dealer domine', 'participants_count' => 55],
                    ['code' => 'eq', 'label' => 'Équilibré', 'participants_count' => 15],
                ],
            ],
            [
                'title' => 'Roulette Numéro chaud',
                'status' => 'disponible',
                'description' => 'Le numéro chaud ressort-il ?',
                'margin' => 0.88,
                'choices' => [
                    ['code' => 'oui', 'label' => 'Oui', 'participants_count' => 33],
                    ['code' => 'non', 'label' => 'Non', 'participants_count' => 47],
                ],
            ],
            [
                'title' => 'Sit & Go 3 joueurs',
                'status' => 'annonce',
                'description' => 'Qui remporte la table ?',
                'margin' => 0.90,
                'choices' => [
                    ['code' => 'p1', 'label' => 'Seat 1', 'participants_count' => 10],
                    ['code' => 'p2', 'label' => 'Seat 2', 'participants_count' => 14],
                    ['code' => 'p3', 'label' => 'Seat 3', 'participants_count' => 6],
                ],
            ],
            [
                'title' => 'Duel High Stakes',
                'status' => 'disponible',
                'description' => 'Match high stakes intense.',
                'margin' => 0.93,
                'choices' => [
                    ['code' => 'A', 'label' => 'Pro A', 'participants_count' => 8],
                    ['code' => 'B', 'label' => 'Pro B', 'participants_count' => 12],
                ],
            ],
            [
                'title' => 'Tournoi 12 joueurs',
                'status' => 'annonce',
                'description' => 'Style de fin probable.',
                'margin' => 0.90,
                'choices' => [
                    ['code' => 'hu', 'label' => 'Heads-Up long', 'participants_count' => 18],
                    ['code' => 'burst', 'label' => 'Eliminations rapides', 'participants_count' => 22],
                    ['code' => 'mid', 'label' => 'Rythme stable', 'participants_count' => 11],
                ],
            ],
            [
                'title' => 'Challenge Gains x2',
                'status' => 'en_cours',
                'description' => 'Atteindra-t-on le double ?',
                'margin' => 0.91,
                'choices' => [
                    ['code' => 'dbl', 'label' => 'Oui doublé', 'participants_count' => 41],
                    ['code' => 'no', 'label' => 'Non', 'participants_count' => 37],
                ],
            ],
        ];

        foreach ($datasets as $ds) {
            $event = BetEvent::create([
                'title' => $ds['title'],
                'description' => $ds['description'] ?? null,
                'status' => $ds['status'] ?? 'disponible',
                'margin' => $ds['margin'] ?? 0.90,
            ]);
            foreach ($ds['choices'] as $c) {
                BetChoice::create([
                    'bet_event_id' => $event->id,
                    'code' => $c['code'],
                    'label' => $c['label'],
                    'participants_count' => $c['participants_count'] ?? 0,
                ]);
            }
        }
    }
}
