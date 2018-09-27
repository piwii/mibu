<?php

namespace App\Controller;

use App\Component\Constant\ModelType;
use App\Component\Handler\PartieHandler;
use App\Component\IO\PartieIO;
use App\Form\PartieType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PartieController extends BaseController
{

    /**
     * @Rest\Get("parties/{partieId}", name="get_partie")
     */
    public function getPartie($partieId)
    {
        return $this->getAction($partieId, ModelType::PARTIE);
    }

    /**
     * @Rest\Get("parties/fiction/{fictionId}", name="get_parties")
     */
    public function getParties(Request $request, $fictionId)
    {
        return $this->createApiResponse(
            $this->getHandler()->getElementsCollection($request, $fictionId, ModelType::PARTIE),
            200,
            $this->getHandler()->generateUrl('get_parties', ['fictionId' => $fictionId], $request->query->get('page', 1))
        );
    }

    /**
     * @Rest\Post("parties", name="post_partie")
     */
    public function postPartie(Request $request)
    {
        return $this->postAction($request, ModelType::PARTIE);
    }


    /**
     * @Rest\Put("parties/{partieId}", name="put_partie")
     */
    public function putPartie(Request $request, $partieId)
    {
        return $this->putAction($request, $partieId, ModelType::PARTIE);

    }

    /**
     * @Rest\Delete("/parties/{partieId}",name="delete_partie")
     */
    public function deletePartie($partieId)
    {
        return $this->deleteAction($partieId, ModelType::PARTIE);
    }

    /**
     * @return PartieHandler
     */
    public function getHandler()
    {
        return new PartieHandler($this->getDoctrine()->getManager(), $this->get('router'));
    }

}