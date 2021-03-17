<?php

namespace Akyos\CoreBundle\Services\UniversignAPI;

use Globalis\Universign\Request\TransactionRequest;
use Globalis\Universign\Request\TransactionSigner;
use Globalis\Universign\Requester;
use Globalis\Universign\Response\TransactionInfo;
use PhpXmlRpc\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Need composer require phpxmlrpc/phpxmlrpc
 *
 * Class UniversignAPI
 * @package Akyos\CoreBundle\Services\UniversignAPI
 */
class UniversignAPI
{
	/**
	 * https://{LOGIN}:{PASSWORD}@ws.universign.eu/sign/rpc/
	 */
	private $urlTest = 'https://sign.test.cryptolog.com/sign/rpc';

	private $urlProd = 'https://ws.universign.eu/sign/rpc';

	/** @var ParameterBagInterface */
	private $parameterBag;

	public function __construct(ParameterBagInterface $parameterBag)
	{
		$this->parameterBag = $parameterBag;
	}

    /**
     * @param $to TransactionSigner|TransactionSigner[]
     * @param $docs
     * @param null $mode
     * @param null $description
     * @param null $language
     * @param null $handwrittenMode
     * @return array
     */
	public function send($to, $docs, $mode = null, $description = null, $language = null, $handwrittenMode = null): array
	{
		$request = new TransactionRequest();

		if (is_array($to)) {
			foreach ($to as $t) {
				$request->addSigner($t);
			}
		} else {
			$request->addSigner($to);
		}

		if (is_array($docs)) {
			foreach ($docs as $doc) {
				$request->addDocument($doc);
			}
		} else {
			$request->addDocument($docs);
		}

		$request
			->setHandwrittenSignatureMode(
				$handwrittenMode ?: TransactionRequest::HANDWRITTEN_SIGNATURE_MODE_BASIC
			)
			->setMustContactFirstSigner(false)
			->setFinalDocRequesterSent(true)
			->setChainingMode(
				TransactionRequest::CHAINING_MODE_EMAIL
			)
			->setDescription($description ?: "Demonstration de la signature Universign")
            ->setLanguage($language ?: 'fr')
        ;

        $response = $this->client($mode ?: 'prod')->requestTransaction($request);

        return [
            'url' => $response->url,
            'id' => $response->id,
        ];
    }

    public function getDocuments($transactionId, $mode = null): array
	{
        $return = [];
        
        if ($transactionId) {
            $requester = $this->client($mode);
            $response = $requester->getTransactionInfo($transactionId);
            if ($response->status === TransactionInfo::STATUS_COMPLETED) {
                $docs = $requester->getDocuments($transactionId);
                foreach ($docs as $doc) {
                    $return[] = [
                    	'doc' => $doc,
                        'name' => $doc->name,
                        'content' => base64_encode($doc->content),
                    ];
                }
            }
        }

        return $return;
    }

    public function client($mode = null): Requester
	{
        $paramMode = $this->parameterBag->get('universign_mode');
        $client = new Client($mode ? ($mode === 'prod' ? $this->urlProd : $this->urlTest) : ($paramMode === 'prod' ? $this->urlProd : $this->urlTest));

        $client->setCredentials(
            $this->parameterBag->get('universign_user'),
            $this->parameterBag->get('universign_password')
        );

        return new Requester($client);
    }

    public function getTransaction($transactionId, $mode = null): TransactionInfo
	{
        $requester = $this->client($mode);

        return $requester->getTransactionInfo($transactionId);
    }
}