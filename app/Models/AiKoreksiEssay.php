<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiKoreksiEssay extends Model
{
    use HasFactory;

    protected $table = 'ai_koreksi_essays';

    protected $fillable = [
        'jawaban_siswa_id',
        'provider',
        'model',
        'score',
        'max_score',
        'score_percentage',
        'reason',
        'strengths',
        'weaknesses',
        'feedback',
        'confidence',
        'status',
        'raw_response',
        'error_message',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'integer',
            'max_score' => 'integer',
            'score_percentage' => 'float',
            'confidence' => 'float',
            'strengths' => 'array',
            'weaknesses' => 'array',
            'raw_response' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function jawabanSiswa()
    {
        return $this->belongsTo(JawabanSiswa::class, 'jawaban_siswa_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function isReview(): bool
    {
        return $this->status === 'review';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }
}
