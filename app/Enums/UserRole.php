<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case InstitutionAdmin = 'institution_admin';
    case PromotionDelegate = 'promotion_delegate';
    case ClassroomDelegate = 'classroom_delegate';
    case Vendor = 'vendor';
    case Alumni = 'alumni';
    case Buyer = 'buyer';
    case Moderator = 'moderator';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super administrador',
            self::InstitutionAdmin => 'Administrador de institución',
            self::PromotionDelegate => 'Delegado de promoción',
            self::ClassroomDelegate => 'Delegado de aula',
            self::Vendor => 'Vendedor / Proveedor',
            self::Alumni => 'Ex-alumno (miembro)',
            self::Buyer => 'Visitante / Comprador',
            self::Moderator => 'Soporte / Moderador',
        };
    }

    /**
     * Roles allowed to access the backoffice panel (/admin).
     *
     * @return list<string>
     */
    public static function backofficeRoles(): array
    {
        return [
            self::SuperAdmin->value,
            self::InstitutionAdmin->value,
            self::PromotionDelegate->value,
            self::ClassroomDelegate->value,
            self::Vendor->value,
            self::Moderator->value,
        ];
    }
}
