<?php

namespace App\Service;

use App\Entity\Coupon;
use App\Repository\CouponRepository;
use Doctrine\ORM\EntityManagerInterface;

class CouponService
{
    private CouponRepository $couponRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        CouponRepository $couponRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->couponRepository = $couponRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Valide et applique un coupon
     */
    public function validateAndApplyCoupon(string $code, float $cartAmount): ?Coupon
    {
        $coupon = $this->couponRepository->findByCode(strtoupper($code));

        if (!$coupon) {
            return null;
        }

        if (!$coupon->isValid()) {
            return null;
        }

        if ($coupon->getMinimumAmount() && $cartAmount < (float)$coupon->getMinimumAmount()) {
            return null;
        }

        return $coupon;
    }

    /**
     * Calcule la réduction pour un coupon
     */
    public function calculateDiscount(Coupon $coupon, float $cartAmount): float
    {
        return $coupon->calculateDiscount($cartAmount);
    }

    /**
     * Applique l'utilisation d'un coupon
     */
    public function useCoupon(Coupon $coupon): void
    {
        $coupon->incrementUsage();
        $this->entityManager->flush();
    }

    /**
     * Récupère tous les coupons valides
     */
    public function getValidCoupons(): array
    {
        return $this->couponRepository->findValidCoupons();
    }

    /**
     * Récupère tous les coupons actifs
     */
    public function getActiveCoupons(): array
    {
        return $this->couponRepository->findActiveCoupons();
    }

    /**
     * Crée un nouveau coupon
     */
    public function createCoupon(array $data): Coupon
    {
        $coupon = new Coupon();
        $coupon->setCode($data['code']);
        $coupon->setType($data['type']);
        $coupon->setValue($data['value']);
        $coupon->setDescription($data['description'] ?? null);
        $coupon->setMinimumAmount($data['minimumAmount'] ?? null);
        $coupon->setMaxUses($data['maxUses'] ?? 0);
        $coupon->setExpiresAt($data['expiresAt'] ?? null);
        $coupon->setIsActive($data['isActive'] ?? true);

        $this->entityManager->persist($coupon);
        $this->entityManager->flush();

        return $coupon;
    }

    /**
     * Désactive un coupon
     */
    public function deactivateCoupon(Coupon $coupon): void
    {
        $coupon->setIsActive(false);
        $this->entityManager->flush();
    }

    /**
     * Vérifie si un code de coupon existe déjà
     */
    public function codeExists(string $code): bool
    {
        return $this->couponRepository->findByCode(strtoupper($code)) !== null;
    }

    /**
     * Génère un code de coupon unique
     */
    public function generateUniqueCode(string $prefix = 'PROMO', int $length = 8): string
    {
        do {
            $code = $prefix . strtoupper(substr(md5(uniqid()), 0, $length));
        } while ($this->codeExists($code));

        return $code;
    }

    /**
     * Récupère les statistiques des coupons
     */
    public function getCouponStats(): array
    {
        $totalCoupons = $this->couponRepository->count([]);
        $activeCoupons = $this->couponRepository->countActiveCoupons();
        $expiredCoupons = count($this->couponRepository->findExpiredCoupons());
        $validCoupons = count($this->couponRepository->findValidCoupons());

        return [
            'total' => $totalCoupons,
            'active' => $activeCoupons,
            'expired' => $expiredCoupons,
            'valid' => $validCoupons,
        ];
    }
}
