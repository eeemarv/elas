<?php declare(strict_types=1);

namespace App\Service;

use Doctrine\DBAL\Connection as Db;
use Redis;

class LogDbService
{
	const MAX_POP = 500;
	const KEY = 'monolog';

	public function __construct(
		protected Db $db,
		protected Redis $redis,
		protected SystemsService $systems_service
	)
	{
	}

	public function update():void
	{
		for ($i = 0; $i < self::MAX_POP; $i++)
		{
			$log_json = $this->redis->lpop(self::KEY);

			if ($log_json === false)
			{
				error_log('no logs');
				return;
			}

			error_log('log');
			error_log($log_json);

			if (!$log_json)
			{
				continue;
			}

			unset($user_id);

			$log = json_decode($log_json, true);

			if (is_null($log)){
				continue;
			}

			$context = $log['context'];
			$extra = $log['extra'];

			if (isset($context['schema']))
			{
				$schema = $context['schema'];

				if (!$schema)
				{
					continue;
				}
			}
			else
			{
				if (!isset($extra['system']))
				{
					continue;
				}

				$system = $extra['system'];
				$schema = $this->systems_service->get_schema($system);
			}

			if (!$schema)
			{
				continue;
			}

			$user_schema = $schema;

			if (isset($extra['os'])
				&& $extra['os'])
			{
				$org_schema = $this->systems_service->get_schema($extra['os']);

				if ($org_schema)
				{
					$user_schema = $org_schema;
				}
			}

			if (isset($extra['logins'])
				&& isset($extra['logins'][$user_schema]))
			{
				$user_id = $extra['logins'][$user_schema];
			}

			$datetime = new \DateTime($log['datetime']);
			$datetime->setTimezone(new \DateTimeZone('UTC'));
			$ts = $datetime->format("Y-m-d H:i:s");

			$insert = [
				'schema'		=> $schema,
				'ts'			=> $ts,
				'type'			=> $log['level_name'],
				'event'			=> $log['message'],
				'data'			=> $log_json,
			];

			if (isset($user_id) && ctype_digit((string) $user_id))
			{
				$insert['user_id'] = $user_id;
				$insert['user_schema'] = $user_schema;
			}

			if (isset($user_id) && $user_id === 'master')
			{
				$insert['is_master'] = true;
			}

			if (isset($log['extra']['ip']))
			{
				$insert['ip'] = $log['extra']['ip'];
			}

			$this->db->insert('xdb.logs', $insert);
		}
	}
}
