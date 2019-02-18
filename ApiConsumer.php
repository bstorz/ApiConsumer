<?php

use Exception as ClientException;
use Zttp\Zttp;

/**
 * Class ApiConsumer
 */
class ApiConsumer {
	private $client;
	private $url;
	private $username = '';
	private $password = '';

	/**
	 * Wps constructor.
	 * @param string $url
	 * @param string $username
	 * @param string $password
	 */
	public function __construct(string $url, string $username = '', string $password = '')
	{
		if ( $username !== '' ) {
			$this->username = $username;
		}
		if ( $password !== '' ) {
			$this->password = $password;
		}
		$token = $this->generateToken();
		$this->client = Zttp::withHeaders(['Authorization' => 'Basic ' . $token]);
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function generateToken(): string
	{
		return base64_encode($this->username . ':' . $this->password);
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	public function get(): array
	{
		// Send
		$request = $this->client->get($this->url);

		return $this->prepareResponse($request);
	}

	/**
	 * @param $response
	 * @return array
	 * @throws ClientException
	 */
	private function prepareResponse($response): array
	{
		if ($response->status() === 200) {
			return $response->json();
		}

		throw new ClientException($response->status() . ': Invalid status code on response.  Got: ' . $response->body());
	}

	/**
	 * Tacks on parameters to a url
	 * @param array $parameters
	 * @return self
	 */
	public function addGetParametersToUrl(array $parameters): self
	{
		$url = $this->url;
		// No parameters?  Go away.
		if (empty($parameters)) {
			return $url;
		}

		// Make sure our string is set up already to accept these
		$url = trim($url, '&');
		if (strpos($url, '?') === false) {
			$url .= '?';
		}
		else {
			$url .= '&';
		}

		// Loop through the parameters and stick em on
		foreach ($parameters as $key => $value) {
			if (\is_array($value)) {
				$value = json_encode($value);
			}
			$url .= $key . '=' . $value . '&';
		}

		$this->url = $url;

		return $this;
	}
}
