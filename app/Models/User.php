<?php
// app/Models/User.php (modifications à apporter au modèle existant)

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * User étendu pour le système hôtelier
 * 
 * ARCHITECTURE :
 * - Chaque user appartient à un tenant (établissement)
 * - Rôle stocké en enum string (plus lisible qu'integer)
 * - Utilise Sanctum pour API tokens (PWA offline sync)
 * 
 * SÉCURITÉ :
 * - Voir section 6.3 du CDC pour le RBAC
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Constantes pour les rôles (évite les magic strings)
    public const ROLE_ADMIN = 'admin';           // Directeur ONG / IT
    public const ROLE_MANAGER = 'manager';       // Directeur d'établissement
    public const ROLE_RECEPTION = 'reception';   // Agent de réception
    public const ROLE_HOUSEKEEPING = 'housekeeping'; // Femme/Valet de chambre

    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',      // ← AJOUTÉ : Liaison à l'établissement
        'role',           // ← AJOUTÉ : RBAC
        'phone',
        'is_active',      // ← AJOUTÉ : Désactivation sans suppression
        'last_login_at',  // ← AJOUTÉ : Audit (section 4.1.2)
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Relation : L'utilisateur appartient à un établissement
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Helper : Vérifie si l'utilisateur a un rôle spécifique
     * Utilisé dans les Blade @can, Policies, et Middleware
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Helper : Vérifie si l'utilisateur a un rôle parmi une liste
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Vérifie les permissions de niveau admin (cross-tenants)
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Vérifie l'accès financier (section 3 : Housekeeping sans accès financier)
     */
    public function canAccessFinancialData(): bool
    {
        return in_array($this->role, [
            self::ROLE_ADMIN,
            self::ROLE_MANAGER,
            self::ROLE_RECEPTION,
        ]);
    }
}