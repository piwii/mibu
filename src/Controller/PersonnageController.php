<?php

namespace App\Controller;

use App\Component\Handler\PersonnageHandler;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use App\Component\Constant\ModelType;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class PersonnageController extends BaseController
{
    /**
     * @var string
     */
    public $modelType = ModelType::PERSONNAGE;

    /**
     * @Rest\Get("personnages/{personnageId}", name="get_personnage")
     */
    public function getPersonnage($personnageId)
    {
        return $this->getAction($personnageId, $this->modelType);
    }

    /**
     * @Rest\Get("personnages/fiction/{fictionId}", name="get_personnages")
     */
    public function getPersonnages(Request $request, $fictionId)
    {
        return $this->createApiResponse(
            $this->getHandler()->getElementsCollection($request, $fictionId, $this->modelType),
            200,
            $this->getHandler()->generateUrl('get_textes', ['fictionId' => $fictionId], $request->query->get('page', 1))
        );
    }

    /**
     * @Rest\Post("personnages", name="post_personnage")
     */
    public function postPersonnage(Request $request)
    {
        return $this->postAction($request, $this->modelType);
    }

    /**
     * @Rest\Put("personnages/{personnageId}", name="put_personnage")
     */
    public function putPersonnage(Request $request, $personnageId)
    {
        return $this->putAction($request, $personnageId, $this->modelType);
    }

    /**
     * @Rest\Delete("/personnages/{personnageId}", name="delete_personnage")
     */
    public function deletePersonnage($personnageId)
    {
        return $this->deleteAction($personnageId, $this->modelType);
    }

    /**
     * @Rest\Post("personnages/generation/fiction={fictionId}/{limit}", name="generate_personnages")
     */
    public function generatePersonnages($fictionId, $limit=10) :array
    {
        if (!$this->getHandler()->generatePersonnages($fictionId, $limit)) {
            throw new BadRequestHttpException(sprintf(
                "Une erreur s'est produite, aucune personnage n'a été généré."
            ));        }

        return $this->redirectToRoute('get_personnages', array('fictionId'=> $fictionId));
    }

    //new function for deleting all the generating characters for a fiction
    public function deleteGenerated()
    {
        return 'Deleted';
    }

    /**
     * @return PersonnageHandler
     */
    public function getHandler()
    {
        return new PersonnageHandler($this->getDoctrine()->getManager(), $this->get('router'));
    }
}