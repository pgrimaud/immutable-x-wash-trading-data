<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiController extends AbstractController
{
    #[Route('/orders', name: 'app_orders')]
    public function index(OrderRepository $orderRepository, Request $request): JsonResponse
    {
        $limit = 100;
        $offset = intval($request->get('page',0)) * $limit;

        $orders = $orderRepository->findOrder($limit, $offset);

        return $this->json($orders);
    }
}
