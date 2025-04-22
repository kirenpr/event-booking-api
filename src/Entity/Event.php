<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\Table(name: "events")]
class Event
{
    #[Groups(['event:read'])]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['event:read'])]
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Event title is required.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "Event title cannot be longer than {{ limit }} characters."
    )]
    private ?string $title = null;

    #[Groups(['event:read'])]
    #[ORM\Column(type: Types::TEXT)]
    #[Assert\Length(
        max: 1000,
        maxMessage: "Event description cannot be longer than {{ limit }} characters."
    )]
    private ?string $description = null;

    #[Groups(['event:read'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: "Event date and time is required.")]
    #[Assert\GreaterThan("today", message: "Event date must be in the future.")]
    #[Assert\Type(\DateTimeInterface::class)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Event capacity is required.")]
    #[Assert\Positive(message: "Event capacity must be greater than 0.")]
    private ?int $capacity = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Event location is required.")]
    #[Assert\Length(
        max: 100,
        maxMessage: "Event location cannot be longer than {{ limit }} characters."
    )]
    private ?string $country = null;

    /**
     * @var Collection<int, Booking>
     */
    #[ORM\OneToMany(targetEntity: Booking::class, mappedBy: 'event', orphanRemoval: true)]
    private Collection $bookings;

    public function __construct()
    {
        $this->bookings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    public function setCapacity(int $capacity): static
    {
        $this->capacity = $capacity;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return Collection<int, Booking>
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): static
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings->add($booking);
            $booking->setEvent($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): static
    {
        if ($this->bookings->removeElement($booking)) {
            // set the owning side to null (unless already changed)
            if ($booking->getEvent() === $this) {
                $booking->setEvent(null);
            }
        }

        return $this;
    }
}
