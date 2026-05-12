<?php
declare(strict_types=1);

namespace Controllers;

use Core\Controller;

class TestController extends Controller
{
    public function test(): void
    {
        echo "✅ El router funciona correctamente!";
    }
}