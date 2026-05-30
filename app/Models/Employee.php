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
}
