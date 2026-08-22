<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\Ai\AiGradingService;

class AiGradingServiceTest extends TestCase
{
    public function test_score_percentage_conversion_20_scale(): void
    {
        // 20 / 20 = 100
        $this->assertEquals(100.0, AiGradingService::calculateScorePercentage(20, 20));

        // 15 / 20 = 75
        $this->assertEquals(75.0, AiGradingService::calculateScorePercentage(15, 20));

        // 10 / 20 = 50
        $this->assertEquals(50.0, AiGradingService::calculateScorePercentage(10, 20));

        // 5 / 20 = 25
        $this->assertEquals(25.0, AiGradingService::calculateScorePercentage(5, 20));

        // 0 / 20 = 0
        $this->assertEquals(0.0, AiGradingService::calculateScorePercentage(0, 20));
    }

    public function test_allowed_scores_constant(): void
    {
        $this->assertEquals([0, 5, 10, 15, 20], AiGradingService::ALLOWED_SCORES);
        $this->assertEquals(20, AiGradingService::MAX_RUBRIC_SCORE);
    }
}
