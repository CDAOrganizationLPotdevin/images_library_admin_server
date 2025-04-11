<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private $id;

    #[ORM\Column(type: "string")]
    private $name;

    #[ORM\Column(type: "string")]
    private $filename; // Le nom du fichier sur le disque

    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function setName(string $name): self { $this->name = $name; return $this; }

    public function getFilename(): ?string { return $this->filename; }
    public function setFilename(string $filename): self { $this->filename = $filename; return $this; }
}
