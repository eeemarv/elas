<?php declare(strict_types=1);

namespace App\Service;

use Redis;
use App\Service\TokenGeneratorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class FormTokenService
{
	protected Request $request;
	protected string $token = '';

	const TTL = 14400; // 4 hours
	const NAME = 'form_token';
	const STORE_PREFIX = 'form_token_';

	public function __construct(
		RequestStack $request_stack,
		protected Redis $redis,
		protected TokenGeneratorService $token_generator_service
	)
	{
		$this->request = $request_stack->getCurrentRequest();
	}

	public function get_posted():string
	{
		return $this->request->request->get(self::NAME, '');
	}

	public function get():string
	{
		if ($this->token === '')
		{
			$this->token = $this->token_generator_service->gen();
			$key = self::STORE_PREFIX . $this->token;
			$this->redis->set($key, '1', self::TTL);
		}

		return $this->token;
	}

	public function get_hidden_input():string
	{
		return '<input type="hidden" name="' . self::NAME . '" value="' . $this->get() . '">';
	}

	public function get_error(bool $incr = true):string
	{
		if ($this->get_posted() === '')
		{
			return 'Het formulier bevat geen form token';
		}

		$key = self::STORE_PREFIX . $this->get_posted();

		$value = $this->redis->get($key);

		if (!$value)
		{
			return 'Het formulier is verlopen';
		}

		if ($value > 1)
		{
			$this->redis->incr($key);
			return 'Een dubbele ingave van het formulier werd voorkomen.';
		}

		if ($incr)
		{
			$this->redis->incr($key);
		}

		return '';
	}

	public function get_param_ary():array
	{
		return [self::NAME => $this->get()];
	}

	public function get_ajax_error(string $form_token):string
	{
		if ($form_token === '')
		{
			return 'Geen form token gedefiniëerd.';
		}
		else if (!$this->redis->get(self::STORE_PREFIX . $form_token))
		{
			return 'Formulier verlopen of ongeldig.';
		}

		return '';
	}

	public function get_query():string
	{
		return $this->request->query->get(self::NAME, '');
	}
}
