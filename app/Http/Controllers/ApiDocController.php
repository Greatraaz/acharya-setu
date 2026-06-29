<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Acharya Setu API",
    version: "1.0.0",
    description: "API documentation"
)]
#[OA\Server(
    url: "http://localhost/acharya-setu/public/api/v1",  // ✅ Fixed URL
    description: "Local server"
)]
#[OA\Server(
    url: "https://api.vantagepointdesign.in/api/v1",           // ✅ Production server
    description: "Production server"
)]
// ── Two separate security schemes ─────────────────────
#[OA\SecurityScheme(
    securityScheme: "menteeAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Login as **Mentee** and paste token here"
)]
#[OA\SecurityScheme(
    securityScheme: "mentorAuth",
    type: "http",
    scheme: "bearer",
    bearerFormat: "JWT",
    description: "Login as **Mentor** and paste token here"
)]


class ApiDocController
{
    #[OA\Get(
        path: "/health",
        summary: "API Health Check",
        responses: [
            new OA\Response(response: 200, description: "OK")
        ]
    )]
    public function index() {}
}