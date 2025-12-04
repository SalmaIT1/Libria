<?php

namespace App\Controller;

use App\Entity\Coupon;
use App\Service\CouponService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/coupons')]
class CouponController extends AbstractController
{
    private CouponService $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
    }

    #[Route('/validate', name: 'api_coupon_validate', methods: ['POST'])]
    public function validateCoupon(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $code = $data['code'] ?? '';
        $cartAmount = (float) ($data['cartAmount'] ?? 0);

        if (!$code) {
            return $this->json([
                'success' => false,
                'message' => 'Code du coupon requis'
            ], 400);
        }

        $coupon = $this->couponService->validateAndApplyCoupon($code, $cartAmount);

        if (!$coupon) {
            return $this->json([
                'success' => false,
                'message' => 'Coupon invalide ou expiré'
            ], 404);
        }

        $discount = $this->couponService->calculateDiscount($coupon, $cartAmount);

        return $this->json([
            'success' => true,
            'coupon' => [
                'code' => $coupon->getCode(),
                'type' => $coupon->getType(),
                'value' => $coupon->getValue(),
                'description' => $coupon->getDescription(),
                'formattedValue' => $coupon->getFormattedValue(),
            ],
            'discount' => $discount,
            'discountFormatted' => number_format($discount, 2, ',', ' ') . ' TND',
            'message' => 'Coupon appliqué avec succès'
        ]);
    }

    #[Route('/list', name: 'api_coupon_list', methods: ['GET'])]
    public function listCoupons(): JsonResponse
    {
        $coupons = $this->couponService->getValidCoupons();
        
        $couponData = [];
        foreach ($coupons as $coupon) {
            $couponData[] = [
                'code' => $coupon->getCode(),
                'type' => $coupon->getType(),
                'value' => $coupon->getValue(),
                'description' => $coupon->getDescription(),
                'formattedValue' => $coupon->getFormattedValue(),
                'minimumAmount' => $coupon->getMinimumAmount(),
                'expiresAt' => $coupon->getExpiresAt()?->format('d/m/Y H:i'),
                'maxUses' => $coupon->getMaxUses(),
                'usedCount' => $coupon->getUsedCount(),
            ];
        }

        return $this->json([
            'success' => true,
            'coupons' => $couponData
        ]);
    }

    #[Route('/stats', name: 'api_coupon_stats', methods: ['GET'])]
    public function getCouponStats(): JsonResponse
    {
        $stats = $this->couponService->getCouponStats();

        return $this->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    #[Route('/generate', name: 'api_coupon_generate', methods: ['POST'])]
    public function generateCoupon(Request $request): JsonResponse
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $data = json_decode($request->getContent(), true);
        $prefix = $data['prefix'] ?? 'PROMO';
        $length = $data['length'] ?? 8;

        $code = $this->couponService->generateUniqueCode($prefix, $length);

        return $this->json([
            'success' => true,
            'code' => $code,
            'message' => 'Code de coupon généré avec succès'
        ]);
    }
}
