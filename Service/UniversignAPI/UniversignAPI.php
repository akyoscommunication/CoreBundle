<?php

namespace Akyos\CoreBundle\Service\UniversignAPI;

use Globalis\Universign\Request\TransactionRequest;
use Globalis\Universign\Request\TransactionSigner;
use Globalis\Universign\Requester;
use Globalis\Universign\Response\TransactionInfo;
use PhpXmlRpc\Client;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Need composer require phpxmlrpc/phpxmlrpc
 * Class UniversignAPI
 * @package Akyos\CoreBundle\Service\UniversignAPI
 */
class UniversignAPI
{
    /**
     * https://{LOGIN}:{PASSWORD}@ws.universign.eu/sign/rpc/
     */
    private string $urlTest = 'https://sign.test.cryptolog.com/sign/rpc';

    private string $urlProd = 'https://ws.universign.eu/sign/rpc';

    private ParameterBagInterface $parameterBag;

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
     * @param bool $mustContactFirstSigner
     * @param bool $finalDocRequesterSent
     * @param bool $finalDocObserverSent
     * @param bool $finalDocSent
     * @return array
     */
    public function send($to, $docs, $mode = null, $description = null, $language = null, $handwrittenMode = null, $mustContactFirstSigner = false, $finalDocRequesterSent = true, $finalDocObserverSent = true, $finalDocSent = false): array
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

        $request->setHandwrittenSignatureMode($handwrittenMode ?: TransactionRequest::HANDWRITTEN_SIGNATURE_MODE_BASIC)->setMustContactFirstSigner($mustContactFirstSigner)->setFinalDocRequesterSent($finalDocRequesterSent)->setFinalDocObserverSent($finalDocObserverSent)->setFinalDocSent($finalDocSent)->setChainingMode(TransactionRequest::CHAINING_MODE_EMAIL)->setDescription($description ?: "Signature Universign")->setLanguage($language ?: 'fr');

        $response = $this->client($mode ?: 'prod')->requestTransaction($request);

        return ['url' => $response->url, 'id' => $response->id,];
    }

    /**
     * @param null $mode
     * @return Requester
     */
    public function client($mode = null): Requester
    {
        $paramMode = $this->parameterBag->get('universign_mode');
        $client = new Client($mode ? ($mode === 'prod' ? $this->urlProd : $this->urlTest) : ($paramMode === 'prod' ? $this->urlProd : $this->urlTest));

        if ($mode === 'test') {
            $client->setCredentials($this->parameterBag->get('universign_user'), $this->parameterBag->get('universign_password_test'));
        } else {
            $client->setCredentials($this->parameterBag->get('universign_user'), $this->parameterBag->get('universign_password'));
        }

        return new Requester($client);
    }

    /**
     * @param $transactionId
     * @param null $mode
     * @return array
     */
    public function getDocuments($transactionId, $mode = null): array
    {
        $return = [];

        if ($transactionId) {
            $requester = $this->client($mode);
            $response = $requester->getTransactionInfo($transactionId);
            if ($response->status === TransactionInfo::STATUS_COMPLETED) {
                $docs = $requester->getDocuments($transactionId);
                foreach ($docs as $doc) {
                    $return[] = ['doc' => $doc, 'name' => $doc->name, 'content' => base64_encode($doc->content),];
                }
            }
        }

        return $return;
    }

    /**
     * @param $transactionId
     * @param null $mode
     * @return TransactionInfo
     */
    public function getTransaction($transactionId, $mode = null): TransactionInfo
    {
        $requester = $this->client($mode);

        return $requester->getTransactionInfo($transactionId);
    }
}