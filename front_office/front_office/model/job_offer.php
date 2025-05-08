<?php

class JobOffer
{
    // Attributes
    private $id;
    private $titre;
    private $entreprise;
    private $emplacement;
    private $description;
    private $date;
    private $type;
    private $Email;

    // Constructor
    public function __construct($titre = null, $entreprise = null, $emplacement = null, $description = null, $date = null, $type = null, $Email = null)
    {
        $this->titre = $titre;
        $this->entreprise = $entreprise;
        $this->emplacement = $emplacement;
        $this->description = $description;
        $this->date = $date;
        $this->type = $type;
        $this->Email = $Email;
    }

    // Show method (for displaying the job offer)
    public function show()
    {
        echo '<table border="1" width="100%">
            <tr align="center">
                <th>Title</th>
                <th>Company</th>
                <th>Location</th>
                <th>Description</th>
                <th>Date</th>
                <th>Type</th>
                <th>Email</th>
            </tr>
            <tr>
                <td>' . $this->titre . '</td>
                <td>' . $this->entreprise . '</td>
                <td>' . $this->emplacement . '</td>
                <td>' . $this->description . '</td>
                <td>' . $this->date . '</td>
                <td>' . $this->type . '</td>
                <td>' . $this->Email . '</td>
            </tr>
        </table>';
    }

    // Getters
    public function getTitre()
    {
        return $this->titre;
    }

    public function getEntreprise()
    {
        return $this->entreprise;
    }

    public function getEmplacement()
    {
        return $this->emplacement;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getEmail()
    {
        return $this->Email;
    }

    // Setters
    public function setTitre($titre)
    {
        $this->titre = $titre;
    }

    public function setEntreprise($entreprise)
    {
        $this->entreprise = $entreprise;
    }

    public function setEmplacement($emplacement)
    {
        $this->emplacement = $emplacement;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setEmail($Email)
    {
        $this->Email = $Email;
    }
}
?>
