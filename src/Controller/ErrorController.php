<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Twig\Environment;

class ErrorController extends AbstractController
{
    public function show(FlattenException $exception, DebugLoggerInterface $logger = null, Environment $twig): Response
    {
        $statusCode = $exception->getStatusCode();
        
        // Determina il template da usare in base al codice di stato
        $template = match ($statusCode) {
            403 => 'error/403.html.twig',
            404 => 'error/404.html.twig',
            500 => 'error/500.html.twig',
            default => 'error/500.html.twig'
        };
        
        return new Response(
            $twig->render($template, [
                'status_code' => $statusCode,
                'status_text' => $exception->getStatusText(),
                'exception' => $exception,
            ]),
            $statusCode
        );
    }
}
