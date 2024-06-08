<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\MedecinRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: MedecinRepository::class)]
class Medecin implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message:"username must not be blank")]
    /**
 * @Assert\Regex(
 *     pattern="/^[a-zA-Z ]+$/",
 *     message="Please enter a value containing only letters and spaces."
 * )
 */
    #[ORM\Column(length: 30)]

    private ?string $username = null;

    #[Assert\NotBlank(message:"email must not be blank")]
    #[Assert\Email(message: "Please enter a valid email address")]
    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[Assert\NotBlank(message:"phone must not be blank")]
    /**
     * @Assert\Regex(
     *     pattern="/^[0-9]+$/",
     *     message="The value '{{ value }}' can only contain numbers."
     * )
     */
    #[ORM\Column(length: 10)]
    private ?string $phone = null;
   
    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    /**
     * @Assert\Length(
     *      min = 6,
     *      minMessage = "Password must be at least {{ limit }} characters long",
     * max=8,
     * maxMessage = "Password must be at least {{ limit }} characters long"
     * )
     */
    private ?string $password = null;


    #[ORM\Column]
    private ?int $token = null;

    #[ORM\Column(length: 254, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(nullable: true)]
    private ?int $code = null;
    #[ORM\Column]
    private array $role = [];

   
    #[ORM\Column(length: 30)]
   
    private ?string $specialite = null;

    
    
    #[ORM\Column(length: 30)]
   
    private ?string $adress = null;

    
    #[Assert\NotBlank(message:"full name must not be blank")]
   /**
 * @Assert\Regex(
 *     pattern="/^[a-zA-Z ]+$/",
 *     message="Please enter a value containing only letters and spaces."
 * )
 */
    #[ORM\Column(length: 30)]
    private ?string $fullname = null;

    public function __construct()
     {
         $this->token = 1;
     }
 

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    
    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $role = $this->role;
        // guarantee every user at least has ROLE_USER
        $role[] = 'user';

        return array_unique($role);
    }

    public function setRoles(array $role): static
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getToken(): ?int
    {
        return $this->token;
    }

    public function setToken(int $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite): static
    {
        $this->specialite = $specialite;

        return $this;
    }

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(string $adress): static
    {
        $this->adress = $adress;

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(string $fullname): static
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function setCode(?int $code): static
    {
        $this->code = $code;

        return $this;
    }
}
