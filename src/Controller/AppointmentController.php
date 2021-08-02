<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class AppointmentController extends AbstractController
{
    
    private $appointment_repository;
    
    public function __construct(\App\Repository\AppointmentsRepository $appointment_repository){
        $this->appointment_repository = $appointment_repository;
    }
    
    
    private function fieldValidation($data, $field){
        
        switch ($field) {
            case 'personal_id':
                $pattern = "/[0-9]{11}/";
                break;
            case 'date':
                $pattern = "/^\d{4}\-(0[1-9]|1[012])\-(0[1-9]|[12][0-9]|3[01])$/";
                break;
            case 'hour':
                $pattern = "/^(?:([01]?\d|2[0-3]):([0-5]?\d):)?([0-5]?\d)$/";
                break;
            case 'appointment_id':
                $pattern = "/[0-9]/";
                break;
            default:
              return false;
        }
          
        $result = (bool)preg_match($pattern, $data);
  
        return $result;
        
    }
    
    
    
    
    /**
     *  @Route("/api/v1/appointments/{appointment_id}/signoff", name="sign_off_from_a_appointment", methods={"PATCH"})
     * aby zrezygnować z zapisanego terminu, należy przekazać ID terminu oraz w celu weryfikacji numer pesel
     */
    public function signOffAppointment($appointment_id, Request $request){
        
        $request_data = json_decode($request->getContent(), true);
        
        $personal_id = '';
        if (isset($request_data['personal_id'])){
            $personal_id  = $request_data['personal_id'];
        }
     
        if (self::fieldValidation($personal_id, 'personal_id') == false || 
            self::fieldValidation($appointment_id, 'appointment_id') == false){
          
            $data = [
                'status' => 400,
                'errors' => "Niewłaściwa walidacja pól",
            ];
        
        } else {
            
        
            $appointment = $this->appointment_repository->findOneBy(
                [
                    'id' => $appointment_id,
                    'ap_personal_id' => $personal_id,
                ]
            ); 

            if ($appointment){

                try{

                    $manager = $this->getDoctrine()->getManager();
                    $appointment->setAppointmentPersonalId(null);

                    $manager->flush();

                    $data = [
                        'status' => 200,
                        'message' => "Poprawne wypisanie z terminu wizyty",
                    ];

                } catch (\Exception $e){

                    $data = [
                        'status' => 500,
                        'errors' => "Nieoczekiwany problem",
                    ];

                }

            } else {

                $data = [
                    'status' => 409,
                    'errors' => "Nie znaleziono zapisu na termin",
                ];

            }
        
        }
        
        return new JsonResponse($data, $data['status']);

    }
    
    
    /**
     *  @Route("/api/v1/appointments/{appointment_id}/signon", name="sign_on_a_appointment", methods={"PATCH"})
     */
    public function signOnAppointment($appointment_id, Request $request){
        
        $request_data = json_decode($request->getContent(), true);
        
        $personal_id = '';
        if (isset($request_data['personal_id'])){
            $personal_id  = $request_data['personal_id'];
        }
        
        // w walidacji peselu należy jeszcze dodać sprawdzenie jego poprawności
        if (self::fieldValidation($personal_id, 'personal_id') == false || 
            self::fieldValidation($appointment_id, 'appointment_id') == false){
          
            $data = [
                'status' => 400,
                'errors' => "Niewłaściwa walidacja pól",
            ];
        
        } else {
        
            $appointment = $this->appointment_repository->findOneBy(['id' => $appointment_id]); 

            if ($appointment){

                if ($appointment->getAppointmentPersonalId() != null){

                    $data = [
                        'status' => 409,
                        'errors' => "Termin już zajęty",
                    ];

                } else {

                    try{

                        $manager = $this->getDoctrine()->getManager();
                        $appointment->setAppointmentPersonalId($personal_id);
                        $manager->flush();

                        $data = [
                            'status' => 200,
                            'message' => "Zapisano na termin ",
                        ];

                    } catch (\Exception $e){

                        $data = [
                            'status' => 500,
                            'errors' => "Nieoczekiwany problem",
                        ];

                    }

                }

            } else {

                $data = [
                    'status' => 409,
                    'errors' => "Nie znaleziono terminu",
                ];

            }
            
        }
        
        return new JsonResponse($data, $data['status']);
        
    }
    
    
    /**
     *  @Route("/api/v1/appointments/{appointment_id}", name="update_appointment", methods={"PATCH"})
     * TODO: walidacja daty i godziny i ID 
     */
    public function updateAppointment($appointment_id, Request $request){
        
        $request_data = json_decode($request->getContent(), true);
     
        $appointment_date = null;
        if (isset($request_data['appointment_date'])){
            $appointment_date = $request_data['appointment_date'];
        }
        
        $appointment_hour = null;
        if (isset($request_data['appointment_hour'])){
            $appointment_hour = $request_data['appointment_hour'];
        }
        
        if (($appointment_date != null && self::fieldValidation($appointment_date, 'date') == false) || 
            ($appointment_hour != null && self::fieldValidation($appointment_hour, 'hour') == false) ||
             self::fieldValidation($appointment_id, 'appointment_id') == false){
            
            $data = [
                'status' => 400,
                'errors' => "Niewłaściwa walidacja pól",
            ];
            
        } else {
        
            $appointment = $this->appointment_repository->findOneBy(['id' => $appointment_id]); 

            if ($appointment){

                if ($appointment->getAppointmentPersonalId() != null){
                    // tutaj powinno być zarządzanie ścieżką, co zrobić, gdy edytowany termin wizyty 
                    // jest juz zajęty w systemie.
                    // Na potrzeby tego api krok ten zostanie pominięty, a ewentualna rezerwacja usunięta
                } 
                
                // tutaj można jeszcze wprowadzić weryfikację, czy po zmianie terminu/godziny wizyty
                // nie dojdzie do sytuacji, że nowy termin i godzina będzie pokrywał się z innym terminem 

                try{

                    $manager = $this->getDoctrine()->getManager();

                    if ($appointment_hour != null) {
                        $appointment->setAppointmentHour(new \DateTime($appointment_hour));
                    }

                    if ($appointment_date != null){
                        $appointment->setAppointmentDate(new \DateTime($appointment_date));
                    }

                    $appointment->setAppointmentPersonalId(null);

                    $manager->flush();

                    $data = [
                        'status' => 200,
                        'errors' => "Update wykonany",
                    ];

                } catch (\Exception $e){

                    $data = [
                        'status' => 500,
                        'errors' => "Nieoczekiwany problem",
                    ];

                }

            } else {

                $data = [
                    'status' => 409,
                    'errors' => "Nie znaleziono terminu",
                ];

            }
            
        }
        
        return new JsonResponse($data, $data['status']);
        
    }
    
    
    /**
     *  @Route("/api/v1/appointments", name="add_appointment", methods={"POST"})
     */
    public function addAppointment(Request $request){
      
        $request_data = json_decode($request->getContent(), true);
       
        $request_date = null;
        if (isset($request_data['appointment_date'])){
            $request_date = $request_data['appointment_date'];
        }
        
        $request_hour = null;
        if (isset($request_data['appointment_hour'])){
            $request_hour = $request_data['appointment_hour'];
        }
      
        if (self::fieldValidation($request_date, 'date') == false || 
            self::fieldValidation($request_hour, 'hour') == false){
          
            $data = [
                'status' => 400,
                'errors' => "Niewłaściwa walidacja pól",
            ];
        
        } else {
        
            // weryfikacja czy taki termin istnieje w bazie 
            $appointment = $this->appointment_repository->findOneBy(
                [
                    'ap_date' => new \DateTime($request_date),
                    'ap_hour' => new \DateTime($request_hour),
                ]
            );

            if ($appointment){

                $data = [
                    'status' => 409, // Konflikt - taki termin już istnieje
                    'success' => "Podany termin już istnieje w systemie",
                ];

            } else {

                try{

                    $manager = $this->getDoctrine()->getManager();
                    $appointment = new \App\Entity\Appointments;

                    $appointment->setAppointmentDate(new \DateTime($request_date));
                    $appointment->setAppointmentHour(new \DateTime($request_hour));

                    $manager->persist($appointment);
                    $manager->flush();

                    $data = [
                        'status' => 201, // HTTP created - utworzono wpis
                        'errors' => "Dodano termin",
                    ];

                } catch (\Exception $e){

                    $data = [
                        'status' => 500,
                        'errors' => "Nieoczekiwany problem",
                    ];

                }

            }

        }
        return new JsonResponse($data, $data['status']);
      
    }
 
    /**
     *  @Route("/api/v1/appointments/{appointment_id}", name="remove_appointment", methods={"DELETE"})
     */
    public function removeAppointment($appointment_id){
        
        $appointment = $this->appointment_repository->findOneBy(['id' => $appointment_id]); 
        
        if (self::fieldValidation($appointment_id, 'appointment_id') == false){
          
            $data = [
                'status' => 400,
                'errors' => "Niewłaściwa walidacja pól",
            ];
            
        } else {
            
            if ($appointment){


                try{
                    $manager = $this->getDoctrine()->getManager();

                    $manager->remove($appointment);
                    $manager->flush();

                    $data = [
                        'status' => 204,
                    ];

                } catch (\Exception $e){

                    $data = [
                        'status' => 500,
                        'errors' => "Nieoczekiwany problem",
                    ];

                }

            } else {

                $data = [
                    'status' => 409,
                    'errors' => "Brak terminu",
                ];

            }
            
        }
        
        return new JsonResponse($data, $data['status']);
        
    }
    
    /**
     *  @Route("/api/v1/appointments/available", name="get_available_appointments", methods={"GET"})
     */
    public function getAvailableAppointments(Request $request){

        $available_appointments = $this->appointment_repository->findBy(['ap_personal_id' => null]);
     
        $return_data = self::generateUserReturnData($available_appointments);
        
        return new JsonResponse($return_data, 200);
        
    }
    
    
    /**
     *  @Route("/api/v1/appointments/reserved", name="get_reserved_appointments", methods={"GET"})
     */
    public function getReservedAppointments(Request $request){
      
        $reserved_appointments = $this->appointment_repository->getReservedAppointments();
        
        $return_data = self::generateAdminReturnData($reserved_appointments);
        
        return new JsonResponse($return_data, 200);
        
    }
    
    /**
     *  @Route("/api/v1/appointments/{appointment_id}", name="get_one_appointment", methods={"GET"})
     */
    public function getOne($appointment_id){
 
        $appointment = $this->appointment_repository->findOneBy(['id' => $appointment_id]); 
       
        $return_data[] = [
            'appointment_id'   => $appointment->getAppointmentId(),
            'appointment_date' => $appointment->getAppointmentDate(),
            'appointment_hour' => $appointment->getAppointmentHour(),
        ];
        
        return new JsonResponse($return_data, 200);
        
    }
    
    /**
     *  @Route("/api/v1/appointments", name="get_all_appointments", methods={"GET"})
     */
    public function getAll(){
        
        $appointments = $this->appointment_repository->findAll(); 
       
        $return_data = self::generateAdminReturnData($appointments);
        
        return new JsonResponse($return_data, 200);
        
    }
    
    
// Metody pomocnicze
    private function generateUserReturnData($appointments){
       
        foreach ($appointments as $appointment){
            
            $return_data[] = [
                'appointment_id'   => $appointment->getAppointmentId(),
                'appointment_date' => $appointment->getAppointmentDate(),
                'appointment_hour' => $appointment->getAppointmentHour(),
            ];
            
        }
        
        return $return_data;
    }
    
    private function generateAdminReturnData($appointments){
        
        foreach ($appointments as $appointment){
            
            $return_data[] = [
                'appointment_id'   => $appointment->getAppointmentId(),
                'appointment_date' => $appointment->getAppointmentDate(),
                'appointment_hour' => $appointment->getAppointmentHour(),
                'appointment_personal_id' => $appointment->getAppointmentPersonalId(),
            ];
            
        }
        
        return $return_data;
        
    }
    
    
    /**
     * @Route("/appointment", name="appointment")
     */
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/AppointmentController.php',
        ]);
    }
    
}
