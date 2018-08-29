<?php

namespace App\Component\Handler;

use App\Component\Fetcher\FictionFetcher;
use App\Component\Fetcher\ItemFetcher;
use App\Component\Fetcher\TexteFetcher;
use App\Component\Hydrator\TexteHydrator;
use App\Component\IO\Pagination\PaginatedCollectionIO;
use App\Component\Serializer\CustomSerializer;
use App\Component\Transformer\TexteTransformer;
use App\Entity\Element\Texte;
use Doctrine\ORM\EntityManager;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Router;


class TexteHandler extends BaseHandler
{

    public function __construct(EntityManager $em, Router $router)
    {
        parent::__construct($em, $router);
        $this->helper = new HelperHandler();
    }

    /**
     * @param $id
     * @return bool|float|int|string
     */
    public function getTexte($id)
    {
        $texte = $this->getFetcher()->fetchTexte($id);
        $texteIO = $this->getTransformer()->convertEntityIntoIO($texte);

        return $this->getSerializer()->serialize($texteIO);
    }

    /**
     * @param $request
     * @return PaginatedCollectionIO
     */
    public function getTextes($request, $fictionId)
    {
        $page = $request->query->get('page', 1);
        $maxPerPage = $request->query->get('maxPerPage', 10);

        $textesIO = [];
        $qb = $this->em->getRepository(Texte::class)->getTextesQueryBuilder();

        $adapter = new DoctrineORMAdapter($qb);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($maxPerPage);
        $pagerfanta->setCurrentPage($page);

        foreach ($pagerfanta->getCurrentPageResults() as $texte){
            $texteIO = $this->getTransformer()->convertEntityIntoIO($texte);

            array_push($textesIO, $texteIO);
        }

        $total = $pagerfanta->getNbResults();

        $collection = new PaginatedCollectionIO($textesIO,$total);

        $collection->addLink('self', $this->generateUrl('get_textes', ['fictionId' => $fictionId], $page));
        $collection->addLink('first', $this->generateUrl('get_textes', ['fictionId' => $fictionId], 1));
        $collection->addLink('last', $this->generateUrl('get_textes', ['fictionId' => $fictionId], $pagerfanta->getNbPages()));

        if ($pagerfanta->hasPreviousPage()) {
            $collection->addLink('previous', $this->generateUrl('get_textes', ['fictionId' => $fictionId], $pagerfanta->getPreviousPage()));
        }

        if ($pagerfanta->hasNextPage()) {
            $collection->addLink('next', $this->generateUrl('get_textes', ['fictionId' => $fictionId], $pagerfanta->getNextPage()));
        }

        return $collection;
    }

    /**
     * @param $data
     * @return \App\Component\IO\TexteIO|mixed
     */
    public function postTexte($data)
    {
        $fictionFetcher = new FictionFetcher($this->em);

        if(!isset($data['fictionId'])) {
            throw new BadRequestHttpException(sprintf(
                "Il n'y a pas de fiction liée à ce texte."
            ));
        }

        $fiction = $fictionFetcher->fetchFiction($data['fictionId']);

        $texte = new Texte($data['titre'], $data['description'], $data['type'], $fiction); //todo : remplacer par un hydrator

        if (isset($data['itemId'])) {

            $itemFetcher = new ItemFetcher($this->em);
            $texte->setItem($itemFetcher->fetchItem($data['itemId']));
        }

        if(!$this->save($texte)) {
            throw new NotFoundHttpException(sprintf(
                "Le texte n'a pas été sauvegardé."
            ));
        }

        return $this->getTransformer()->convertEntityIntoIO($texte);
    }
    
    public function putTexte($texteId, $data)
    {
        //fetch texte and check if exists
        $texte = $this->getFetcher()->fetchTexte($texteId);

        //fetch fiction
        $fictionFetcher = new FictionFetcher($this->em);

        if(!isset($data['fictionId'])) {
            throw new BadRequestHttpException(sprintf(
                "Il n'y a pas de fiction liée à ce texte."
            ));
        }

        $data['fiction'] = $fictionFetcher->fetchFiction($data['fictionId']);

        //change the data
        $texte = $this->getHydrator()->hydrateTexte($texte, $data);

        //save
        $this->save($texte);

        //transform into IO
        $texteIO = $this->getTransformer()->convertEntityIntoIO($texte);

        return $texteIO;
    }


    /**
     * @param $texteId
     * @return JsonResponse
     */
    public function deleteTexte($texteId)
    {
        $texte = $this->getFetcher()->fetchTexte($texteId);
        $this->em->remove($texte);
        $this->em->flush();

        return new JsonResponse('Suppression du texte '.$texteId.'.', 200);
    }

    /**
     * @return TexteFetcher
     */
    public function getFetcher(): TexteFetcher
    {
        return new TexteFetcher($this->em);
    }

    /**
     * @return TexteHydrator
     */
    public function getHydrator(): TexteHydrator
    {
        return new TexteHydrator();
    }

    /**
     * @return TexteTransformer
     */
    public function getTransformer() : TexteTransformer
    {
        return new TexteTransformer();
    }

    /**
     * @return CustomSerializer
     */
    public function getSerializer(): CustomSerializer
    {
        return new CustomSerializer();
    }



}