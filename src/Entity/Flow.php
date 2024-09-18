<?php

namespace App\Entity;

use App\Repository\FlowRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[Assert\GroupSequence(['Flow', 'Strict'])]
#[ORM\Entity(repositoryClass: FlowRepository::class)]
class Flow
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[ORM\ManyToOne(inversedBy: 'giftsGived')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Person $personFrom = null;

    #[Assert\NotBlank]
    #[ORM\ManyToOne(inversedBy: 'giftsReceived')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Person $personTo = null;

    #[ORM\ManyToOne(inversedBy: 'flows')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Gift $gift = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private $receivedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $importPersonFromCountry = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $importPersonToCountry = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $importPersonTo = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $importPersonFrom = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $importPersonToCategory = null;

    /**
     * Traits
     */
    use BlameableEntity;
    use TimestampableEntity;
    use SoftDeleteableEntity;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPersonFrom(): ?Person
    {
        return $this->personFrom;
    }

    public function setPersonFrom(?Person $personFrom): self
    {
        $this->personFrom = $personFrom;

        return $this;
    }

    public function getPersonTo(): ?Person
    {
        return $this->personTo;
    }

    public function setPersonTo(?Person $personTo): self
    {
        $this->personTo = $personTo;

        return $this;
    }

    public function getGift(): ?Gift
    {
        return $this->gift;
    }

    public function setGift(?Gift $gift): self
    {
        $this->gift = $gift;

        return $this;
    }

    public function getReceivedAt(): ?\DateTime
    {
        return $this->receivedAt;
    }

    public function setReceivedAt(\DateTime $receivedAt): self
    {
        $this->receivedAt = $receivedAt;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    #[Assert\IsTrue(
        message: 'Receiver and Giver must not be same person',
        groups: ['Strict'],
    )]
    public function isSamePerson()
    {
        return ($this->personTo !== $this->personFrom);
    }

    public function getImportPersonFromCountry(): ?string
    {
        return $this->importPersonFromCountry;
    }

    public function setImportPersonFromCountry(?string $importPersonFromCountry): self
    {
        $this->importPersonFromCountry = $importPersonFromCountry;

        return $this;
    }

    public function getImportPersonToCountry(): ?string
    {
        return $this->importPersonToCountry;
    }

    public function setImportPersonToCountry(?string $importPersonToCountry): self
    {
        $this->importPersonToCountry = $importPersonToCountry;

        return $this;
    }

    public function getImportPersonTo(): ?string
    {
        return $this->importPersonTo;
    }

    public function setImportPersonTo(?string $importPersonTo): self
    {
        $this->importPersonTo = $importPersonTo;

        return $this;
    }

    public function getImportPersonFrom(): ?string
    {
        return $this->importPersonFrom;
    }

    public function setImportPersonFrom(?string $importPersonFrom): self
    {
        $this->importPersonFrom = $importPersonFrom;

        return $this;
    }

    public function getImportPersonToCategory(): ?string
    {
        return $this->importPersonToCategory;
    }

    public function setImportPersonToCategory(string $importPersonToCategory): self
    {
        $this->importPersonToCategory = $importPersonToCategory;

        return $this;
    }
}
