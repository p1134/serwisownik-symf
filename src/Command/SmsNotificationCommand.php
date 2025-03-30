<?php
namespace App\Command;

use App\Repository\VehicleRepository;
use App\Service\SmsService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface; 

class SmsNotificationCommand extends Command
{
    private $smsService;
    private $vehicleRepository;

    public function __construct(SmsService $smsService, VehicleRepository $vehicleRepository)
    {
        parent::__construct();
        $this->smsService = $smsService;
        $this->vehicleRepository = $vehicleRepository;
    }

    // protected static $defaultName = 'app:sms-notification';

    protected function configure()
    {
        $this->setName('app:sms-notification');
        $this->setDescription('Wysyła przypomnienienie Sms');
    }

    public function selectUsers (VehicleRepository $vehicles){
        $insurance = null;
        $service = null;

        $vehiclesArrayService = $vehicles->smsServiceNotification();
        if($vehiclesArrayService != null){

            foreach ($vehiclesArrayService as $key => $value) {
                $vehicle = $vehiclesArrayService[$key];
                $brand = $vehicle->getBrand();
                $model = $vehicle->getModel();
                $numberPlate = $vehicle->getNumberPlate();
                $service = ($vehicle->getService())->format('d-m-Y');

                $owner = $vehicle->getOwner();
    
                $this->smsService->sendSms($owner->getPhoneNumber(), 'Przegląd pojazdu '.$brand.' '.$model.' o numerze rejestracyjnym '.$numberPlate.' kończy się '.$service);
    
            }
        }

        $vehiclesArrayInsurance = $vehicles->smsInsuranceNotification();
        if($vehiclesArrayInsurance != null){

            foreach ($vehiclesArrayInsurance as $key => $value) {
                $vehicle = $vehiclesArrayInsurance[$key];
                $brand = $vehicle->getBrand();
                $model = $vehicle->getModel();
                $numberPlate = $vehicle->getNumberPlate();
                $insurance = ($vehicle->getInsurance())->format('d-m-Y');

                $owner = $vehicle->getOwner();
    
                $this->smsService->sendSms($owner->getPhoneNumber(), 'Ubezpieczenie pojazdu '.$brand.' '.$model.' o numerze rejestracyjnym '.$numberPlate.' kończy się '.$insurance);
    
            }
        }
    }
    protected function execute(InputInterface $input, OutputInterface $output):int
    {
        $this->selectUsers($this->vehicleRepository);
        
        $output->writeln('Powiadomienia SMS zostały wysłane.');

        return Command::SUCCESS;
    }
}