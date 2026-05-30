<?php

namespace App\Models;

use App\Enums\AccountStateType;
use App\Enums\ContractType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'firstName',
        'lastName',
        'phone',
        'birthDate',
        'hireDate',
        'gender',
        'jobTitle',
        'department_id',
        'contractType',
        'grossSalary',
        'status',
        'photo',
    ];

    protected $casts = [
        'birthDate' => 'date',
        'hireDate' => 'date',
        'contractType' => ContractType::class,
        'status' => AccountStateType::class,
        'grossSalary' => 'float',
    ];

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            AccountStateType::ACTIVE => __('Active'),
            AccountStateType::INACTIVE => __('Inactive'),
            AccountStateType::BLOCKED => __('Blocked'),
            AccountStateType::DELETED => __('Archived'),
            AccountStateType::NOTICE => __('Notice Period'),
            default => $this->status->value,
        };
    }

    public function getContractLabelAttribute(): string
    {
        return match($this->contractType) {
            ContractType::CDI => 'CDI',
            ContractType::CDD => 'CDD',
            ContractType::Stage => __('Internship'),
            ContractType::Alternance => __('Apprenticeship'),
            ContractType::Freelance => 'Freelance',
            default => $this->contractType->value,
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    public function managedDepartments(): HasMany
    {
        return $this->hasMany(Department::class, 'manager_id');
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }

    public function getLeaveBalanceAttribute(): float
    {
        $monthsWorked = $this->hireDate->diffInMonths(now());
        $totalEarned = $monthsWorked * 2.5;
        
        $totalUsed = $this->leaves()
            ->where('type', \App\Enums\LeaveType::PAID)
            ->where('status', \App\Enums\LeaveStateType::APPROVED)
            ->sum('days_count');
            
        return max(0, $totalEarned - $totalUsed);
    }
}
