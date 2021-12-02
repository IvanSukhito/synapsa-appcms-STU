<?php

namespace App\Jobs;

use App\Codes\Logic\DoctorLogic;
use App\Codes\Logic\LabLogic;
use App\Codes\Logic\ProductLogic;
use App\Codes\Logic\PushNotification;
use App\Codes\Logic\UserLogic;
use App\Codes\Models\V1\AppointmentDoctor;
use App\Codes\Models\V1\AppointmentDoctorProduct;
use App\Codes\Models\V1\BookNurse;
use App\Codes\Models\V1\Payment;
use App\Codes\Models\V1\Product;
use App\Codes\Models\V1\Service;
use App\Codes\Models\V1\SetJob;
use App\Codes\Models\V1\Shipping;
use App\Codes\Models\V1\Transaction;
use App\Codes\Models\V1\TransactionDetails;
use App\Codes\Models\V1\Users;
use App\Codes\Models\V1\UsersAddress;
use App\Codes\Models\V1\LabCart;
use App\Codes\Models\V1\UsersCartDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userIds;
    protected $title;
    protected $message;
    protected $target;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    /**
     * @param $userIds
     * @param $title
     * @param $message
     * @param array $target
     *
     * @return void
     */
    public function __construct($userIds, $title, $message, array $target = array())
    {
        $this->userIds = $userIds;
        $this->title = $title;
        $this->message = $message;
        $this->target = $target;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $pushNotification = new PushNotification();
        $pushNotification->sendingNotification($this->userIds, $this->title, $this->message, $this->target);
    }

}
