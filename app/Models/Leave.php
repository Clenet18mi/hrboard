<?php

namespace App\Models;

use App\Enums\LeaveStateType;
use App\Enums\LeaveType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'type',
        'start_date',
        'end_date',
        'days_count',
        'reason',
        'status',
        'hr_comment',
        'approved_by',
    ];

    protected $casts = [
        'type' => LeaveType::class,
        'status' => LeaveStateType::class,
        'start_date' => 'date',
        'end_date' => 'date',
        'days_count' => 'integer',
    ];

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            LeaveType::PAID => __('Paid Leave'),
            LeaveType::RTT => 'RTT',
            LeaveType::SICK => __('Sick Leave'),
            LeaveType::UNPAID => __('Unpaid Leave'),
            LeaveType::EXCEPTIONAL => __('Exceptional Leave'),
            default => $this->type->value,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            LeaveStateType::PENDING => __('Pending'),
            LeaveStateType::APPROVED => __('Approved'),
            LeaveStateType::REJECTED => __('Rejected'),
            default => $this->status->value,
        };
    }

    public static function calculateDays($startDate, $endDate): int
    {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        $days = 0;

        while ($start <= $end) {
            if (!$start->isWeekend()) {
                $days++;
            }
            $start->addDay();
        }

        return $days;
    }

    public function scopeOverlapping($query, $employeeId, $startDate, $endDate)
    {
        return $query->where('employee_id', $employeeId)
            ->where('status', LeaveStateType::APPROVED)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($sq) use ($startDate, $endDate) {
                        $sq->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            });
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
