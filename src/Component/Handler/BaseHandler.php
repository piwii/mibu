<?php

namespace App\Component\Handler;

use App\Component\Constant\ModelType;
use App\Component\Fetcher\FictionFetcher;
use App\Component\Fetcher\PersonnageFetcher;
use App\Component\Hydrator\PersonnageHydrator;
use App\Component\Hydrator\TexteHydrator;
use App\Component\Hydrator\FictionHydrator;
use App\Component\Serializer\CustomSerializer;
use App\Component\Transformer\PersonnageTransformer;
use App\Entity\Element\Personnage;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Routing\Router;

class BaseHandler
{
    protected $em;
    protected $router;

    /**
     * BaseHandler constructor.
     * @param EntityManager $em
     * @param Router $router
     */
    public function __construct(EntityManager $em, Router $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    /**
     * @param $route
     * @param array $params
     * @param $targetPage
     * @return string
     */
    public function generateUrl($route, array $params, $targetPage)
    {
        return $this->router->generate(
            $route,
            array_merge(
                $params,
                array('page' => $targetPage)
            )
        );
    }

    public function generateSimpleUrl($route, array $params)
    {
        return $this->router->generate(
            $route,
            array_merge(
                $params
            )
        );
    }

    /**
     * @param $entity
     * @return bool
     */
    public function save($entity)
    {
        $this->em->persist($entity);
        $this->em->flush();

        return true;
    }

    /**
     * @return CustomSerializer
     */
    public function getSerializer(): CustomSerializer
    {
        return new CustomSerializer();
    }

    /**
     * @param $modelType
     * @return PersonnageFetcher
     */
    public function getEntityFetcher($modelType) {

        $className = 'App\Component\Fetcher\\'.ucfirst($modelType).'Fetcher';
        return new $className($this->em);
    }

    /**
     * @param $modelType
     * @return mixed
     */
    public function getEntityHydrator($modelType)
    {
        $className = 'App\Component\Hydrator\\'.ucfirst($modelType).'Hydrator';
        return new $className();
    }

    /**
     * @param $modelType
     * @return mixed
     */
    public function getEntityTransformer($modelType)
    {
        $className = 'App\Component\Transformer\\'.ucfirst($modelType).'Transformer';
        return new $className();
    }

    /**
     * @param $fictionId
     * @return mixed
     */
    public function getFiction($fictionId)
    {
        $fictionFetcher = new FictionFetcher($this->em);

        if(!$fictionId) {
            throw new BadRequestHttpException(sprintf(
                "Il n'y a pas de fiction liée à cet élément."
            ));
        }

        return $fictionFetcher->fetchFiction($fictionId);
    }

    /**
     * @param $entityId
     * @param $modelType
     * @return mixed
     */
    public function getEntity($entityId, $modelType)
    {
        $functionName = 'fetch'.$modelType;
        return $this->getEntityTransformer($modelType)->convertEntityIntoIO($this->getEntityFetcher($modelType)->$functionName($entityId));
    }

    /**
     * @param $data
     * @param $modelType
     * @return \App\Component\IO\PersonnageIO|mixed
     */
    public function postEntity($data, $modelType)
    {
        switch ($modelType) {
            case ModelType::PERSONNAGE:
                $entity = new Personnage($data['titre'], $data['description'], isset($data['itemId']));
                break;
            default:
                throw new UnauthorizedHttpException(sprintf(
                    "Aucun modelType n'est renseigné."
                ));
        }
        return $this->changeData($data, $entity, $modelType);
    }

    /**
     * @param $personnageId
     * @param $data
     * @param $modelType
     * @return \App\Component\IO\PersonnageIO|mixed
     */
    public function putEntity($personnageId, $data, $modelType)
    {
        $functionName = 'fetch'.$modelType;
        $entity = $this->getEntityFetcher($modelType)->$functionName($personnageId);

        return $this->changeData($data, $entity, $modelType);

    }

    /**
     * @param $personnageId
     * @param $modelType
     * @return JsonResponse
     */
    public function deleteEntity($personnageId, $modelType)
    {
        $functionName = 'fetch'.$modelType;
        $this->em->remove($this->getEntityFetcher($modelType)->$functionName($personnageId));
        $this->em->flush();

        return new JsonResponse('Suppression du texte '.$personnageId.'.', 200);
    }

    /**
     * @param $data
     * @param $entity
     * @param $modelType
     * @return \App\Component\IO\PersonnageIO|mixed
     */
    public function changeData($data, $entity, $modelType)
    {
        $hydrator = $this->getEntityHydrator(ModelType::PERSONNAGE);
        $transformer = $this->getEntityTransformer(ModelType::PERSONNAGE);

        if(!isset($data['fictionId'])){
            throw new UnauthorizedHttpException(sprintf(
                "Le champ fictionId n'est pas renseigné."
            ));
        }

        $data['fiction'] = $this->getFiction($data['fictionId']);

        $functionName = 'hydrate'.$modelType;
        $entity = $hydrator->$functionName($entity, $data);

        $this->save($entity);

        return $transformer->convertEntityIntoIO($entity);
    }

}