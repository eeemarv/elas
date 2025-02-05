<?php declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class IntersystemsExtension extends AbstractExtension
{
	public function getFunctions():array
	{
		return [
			new TwigFunction('intersystem_schemas', [IntersystemsRuntime::class, 'get_schemas']),
		];
	}
}
