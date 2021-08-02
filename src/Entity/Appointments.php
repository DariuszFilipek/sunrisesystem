<?php

namespace App\Entity;

use App\Repository\AppointmentsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AppointmentsRepository::class)
 */
class Appointments
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @ORM\Column(type="date") 
     */
    private $ap_date;
    
    /**
     * @ORM\Column(type="time")
     */
    private $ap_hour;
    
    /**
     * @ORM\Column(type="string", length=11, nullable=true, options={"default" : null})
     */
    private $ap_personal_id;
    
    
    
// ----- GETTERS ----- //
    public function getAppointmentId(){
        
        return $this->id;
        
    }
    
    public function getAppointmentDate(){
        
        return $this->ap_date->format('Y-m-d');
        
    }
    
    public function getAppointmentHour(){
        
        return $this->ap_hour->format('H:i:s');
        
    }
    
    public function getAppointmentPersonalId(){
        
        return $this->ap_personal_id;
        
    }

    
// ----- SETTERS ----- //
    public function setAppointmentDate($appointment_date){
        
        $this->ap_date = $appointment_date;
        
    }
    
    public function setAppointmentHour($appointment_hour){
        
        $this->ap_hour = $appointment_hour;
        
    }
    
    public function setAppointmentPersonalId($appointment_personal_id){
        
        $this->ap_personal_id = $appointment_personal_id;
        
    }
    

    
}
