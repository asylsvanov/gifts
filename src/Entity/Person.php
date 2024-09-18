<?php

namespace App\Entity;

use App\Repository\PersonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Blameable\Traits\BlameableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use App\Entity\Gift;
use App\Entity\Preference;
use App\Entity\Attachment;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
class Person
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $prefix = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $surname = null;

    #[ORM\Column(length: 50)]
    private ?string $country = null;

    #[ORM\Column(length: 20)]
    private ?string $sex = null;

    #[ORM\Column(nullable: true)]
    private ?int $age = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $birthAt = null;

    #[ORM\Column(type: Types::ARRAY)]
    private array $language = [];

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $summary = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isActive = null;

    #[ORM\Column(length: 600)]
    #[Gedmo\Slug(fields: ['firstName', 'lastName', 'id'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $socialProfiles = [];

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private array $category = [];

    #[ORM\ManyToMany(targetEntity: Preference::class)]
    private Collection $preferences;

    #[ORM\OneToMany(targetEntity: Attachment::class, mappedBy: 'person', cascade: ["persist", "remove"])]
    private Collection $attachments;

    #[ORM\OneToMany(mappedBy: 'personFrom', targetEntity: Flow::class)]
    private Collection $giftsGived;

    #[ORM\OneToMany(mappedBy: 'personTo', targetEntity: Flow::class)]
    private Collection $giftsReceived;

    public function __construct()
    {
        $this->preferences = new ArrayCollection();
        $this->attachments = new ArrayCollection();
        $this->giftsGived = new ArrayCollection();
        $this->giftsReceived = new ArrayCollection();
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

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(?string $prefix): self
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(?string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getSex(): ?string
    {
        return $this->sex;
    }

    public function setSex(string $sex): self
    {
        $this->sex = $sex;

        return $this;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }

    public function setAge(int $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getBirthAt(): ?\DateTimeImmutable
    {
        return $this->birthAt;
    }

    public function setBirthAt(\DateTimeImmutable $birthAt): self
    {
        $this->birthAt = $birthAt;

        return $this;
    }

    public function getLanguage(): array
    {
        return $this->language;
    }

    public function setLanguage(array $language): self
    {
        $this->language = $language;

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

    public function getSocialProfiles(): array
    {
        return $this->socialProfiles;
    }

    public function setSocialProfiles(?array $socialProfiles): self
    {
        $this->socialProfiles = $socialProfiles;

        return $this;
    }

    public function getCategory(): array
    {
        return $this->category;
    }

    public function setCategory(?array $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function __toString(){
        return $this->firstName. " " . $this->lastName;
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
     * @return Collection<int, Attachment>
     */
    public function getAttachments(): Collection
    {
        return $this->attachments;
    }

    public function addAttachment(Attachment $attachment): self
    {
        if (!$this->attachments->contains($attachment)) {
            $this->attachments->add($attachment);
            $attachment->setPerson($this);
        }
        return $this;
    }

    public function removeAttachment(Attachment $attachment): self
    {
        if ($this->attachments->removeElement($attachment)) {
            // set the owning side to null (unless already changed)
            if ($attachment->getPerson() === $this) {
                $attachment->setPerson(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Flow>
     */
    public function getGiftsGived(): Collection
    {
        return $this->giftsGived;
    }

    public function addGiftsGived(Flow $giftsGived): self
    {
        if (!$this->giftsGived->contains($giftsGived)) {
            $this->giftsGived->add($giftsGived);
            $giftsGived->setPersonFrom($this);
        }

        return $this;
    }

    public function removeGiftsGived(Flow $giftsGived): self
    {
        if ($this->flows->removeElement($giftsGived)) {
            // set the owning side to null (unless already changed)
            if ($giftsGived->getPersonFrom() === $this) {
                $giftsGived->setPersonFrom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Flow>
     */
    public function getGiftsReceived(): Collection
    {
        return $this->giftsReceived;
    }

    public function addGiftsReceived(Flow $giftsReceived): self
    {
        if (!$this->giftsReceived->contains($giftsReceived)) {
            $this->giftsReceived->add($giftsReceived);
            $giftsReceived->setPersonTo($this);
        }

        return $this;
    }

    public function removeGiftsReceived(Flow $giftsReceived): self
    {
        if ($this->giftsReceived->removeElement($giftsReceived)) {
            // set the owning side to null (unless already changed)
            if ($giftsReceived->getPersonTo() === $this) {
                $giftsReceived->setPersonTo(null);
            }
        }

        return $this;
    }


}
