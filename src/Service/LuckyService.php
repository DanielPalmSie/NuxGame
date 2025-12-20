<?php

namespace App\Service;

use App\Domain\Entity\Attempt;
use App\Repository\AttemptRepository;
use App\Util\HttpException;

class LuckyService
{
    public function __construct(
        private AttemptRepository $attempts,
        private LinkService $links
    ) {
    }

    public function tryLuck(string $token): Attempt
    {
        $link = $this->links->requireActiveLink($token);

        $number = random_int(1, 1000);
        $isWin = $number % 2 === 0;
        $amount = 0.0;
        $result = 'lose';

        if ($isWin) {
            $result = 'win';
            if ($number > 900) {
                $amount = $number * 0.7;
            } elseif ($number > 600) {
                $amount = $number * 0.5;
            } elseif ($number > 300) {
                $amount = $number * 0.3;
            } else {
                $amount = $number * 0.1;
            }
        }

        return $this->attempts->create($link->getUserId(), $link->getId(), $number, $result, round($amount, 2));
    }
}
