<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Models\Prediction;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Attributs protégés (Encapsulation : on ne peut pas les modifier sans passer par Laravel)
     */
    protected $fillable = [
        'email', 'password', 'firstname', 'lastname', 'ville', 'adresse', 'phone', 'bio', 'role', 'verified'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'password' => 'hashed',
    ];

    protected $dates = ['deleted_at']; // 3. Champ de date
    // --- ENCAPSULATION DES RÔLES ---

    /**
     * Vérifie si l'utilisateur possède les privilèges administrateur.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Définit le rôle de l'utilisateur de manière sécurisée.
     */
    public function setAsAdmin(): void
    {
        $this->role = 'admin';
        $this->save();
    }

    // --- ENCAPSULATION DU PROFIL ---

    /**
     * Retourne le nom complet (Concaténation encapsulée)
     */
    public function getFullNameAttribute(): string
    {
        return ucfirst($this->firstname) . ' ' . strtoupper($this->lastname);
    }

    /**
     * Mutateur pour l'email (Encapsulation : s'assure que l'email est toujours en minuscule)
     */
    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = strtolower($value);
    }

    public function predictions(): HasMany
    {
        return $this->hasMany(Prediction::class);
    }

    // --- GESTION DES TIMESTAMPS ---
    // Laravel gère déjà created_at et updated_at.
    // Tu peux y accéder via $user->created_at.
}
