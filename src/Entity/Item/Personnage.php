<?php

namespace App\Entity\Item;

use App\Entity\Modele\AbstractItem;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="personnage")
 */
class Personnage extends AbstractItem
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $annee_naissance;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $annee_mort;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $prenom;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    private $genre;

    public function __construct($nom, $description)
    {
        parent::__construct();
        $this->setTitre($nom);
        $this->setDescription($description);
    }

    /**
     * @return int
     */
    public function getAnneeNaissance()
    {
        return $this->annee_naissance;
    }

    /**
     * @param int $annee_naissance
     */
    public function setAnneeNaissance($annee_naissance)
    {
        $this->annee_naissance = $annee_naissance;
    }

    /**
     * @return int
     */
    public function getAnneeMort()
    {
        return $this->annee_mort;
    }

    /**
     * @param int $annee_mort
     */
    public function setAnneeMort($annee_mort)
    {
        $this->annee_mort = $annee_mort;
    }


    /**
     * @return mixed
     */
    public function getDateMort()
    {
        return $this->date_mort;
    }

    /**
     * @param mixed $date_mort
     */
    public function setDateMort($date_mort)
    {
        $this->date_mort = $date_mort;
    }

    /**
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * @param string $prenom
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    }

    /**
     * @return string
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * @param string $genre
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;
    }

}