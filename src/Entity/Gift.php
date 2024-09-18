<?php

namespace App\Entity;

use App\Repository\GiftRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use App\Entity\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;

#[ORM\Entity(repositoryClass: GiftRepository::class)]
class Gift
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $size = null;

    #[ORM\Column(nullable: true)]
    private ?int $price = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $originCountry = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column]
    private ?bool $isAvailable = null;

    #[ORM\Column(nullable: true)]
    private ?int $counter = null;

    #[ORM\Column(length: 255)]
    private ?string $material = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column(length: 600, unique: true)]
    #[Gedmo\Slug(fields: ['title', 'id'])]
    private ?string $slug = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $author = null;

    #[ORM\ManyToMany(targetEntity: Preference::class, inversedBy: 'gifts')]
    private Collection $preferences;

    #[ORM\OneToMany(mappedBy: 'gift', targetEntity: Photo::class, cascade: ["persist", "remove"])]
    private Collection $photos;

    #[ORM\Column(nullable: true)]
    private ?int $daysToDelivery = null;

    #[ORM\OneToMany(mappedBy: 'gift', targetEntity: Flow::class)]
    private Collection $flows;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $gender = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $generation = null;

    public function __construct()
    {
        $this->preferences = new ArrayCollection();
        $this->photos = new ArrayCollection();
        $this->isActive = 1;
        $this->flows = new ArrayCollection();
    }

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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public function setSize(string $size): self
    {
        $this->size = $size;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getOriginCountry(): ?string
    {
        return $this->originCountry;
    }

    public function setOriginCountry(string $originCountry): self
    {
        $this->originCountry = $originCountry;

        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $summary): self
    {
        $this->summary = $summary;

        return $this;
    }

    public function isIsAvailable(): ?bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): self
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

    public function getCounter(): ?int
    {
        return $this->counter;
    }

    public function setCounter(?int $counter): self
    {
        $this->counter = $counter;

        return $this;
    }

    public function getMaterial(): ?string
    {
        return $this->material;
    }

    public function setMaterial(string $material): self
    {
        $this->material = $material;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function __toString(){
        return $this->title;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, Preference>
     */
    public function getPreferences(): Collection
    {
        return $this->preferences;
    }

    public function addPreference(Preference $preference): self
    {
        if (!$this->preferences->contains($preference)) {
            $this->preferences->add($preference);
        }

        return $this;
    }

    public function removePreference(Preference $preference): self
    {
        $this->preferences->removeElement($preference);

        return $this;
    }

    /**
     * @return Collection<int, Photo>
     */
    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function addPhoto(Photo $photo): self
    {
        if (!$this->photos->contains($photo)) {
            $this->photos->add($photo);
            $photo->setGift($this);
        }

        return $this;
    }

    public function removePhoto(Photo $photo): self
    {
        if ($this->photos->removeElement($photo)) {
            // set the owning side to null (unless already changed)
            if ($photo->getGift() === $this) {
                $photo->setGift(null);
            }
        }

        return $this;
    }

    public function getDaysToDelivery(): ?int
    {
        return $this->daysToDelivery;
    }

    public function setDaysToDelivery(?int $daysToDelivery): self
    {
        $this->daysToDelivery = $daysToDelivery;

        return $this;
    }

    /**
     * @return Collection<int, Flow>
     */
    public function getFlows(): Collection
    {
        return $this->flows;
    }

    public function addFlow(Flow $flow): self
    {
        if (!$this->flows->contains($flow)) {
            $this->flows->add($flow);
            $flow->setGift($this);
        }

        return $this;
    }

    public function removeFlow(Flow $flow): self
    {
        if ($this->flows->removeElement($flow)) {
            // set the owning side to null (unless already changed)
            if ($flow->getGift() === $this) {
                $flow->setGift(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getGeneration(): ?string
    {
        return $this->generation;
    }

    public function setGeneration(string $generation): self
    {
        $this->generation = $generation;

        return $this;
    }
}
