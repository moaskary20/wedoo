<?php
namespace Config;
class ApiResponseAndNotificationStrings extends \CodeIgniter\Config\BaseConfig
{
    // Registration Request
    public $registrationRequestApproval = "Approval of Registration Request";
    public $registrationRequestApprovedMessage = "Your registration request has been approved. You can now access all features of our platform.";
    public $registrationRequestRejection = "Rejection of Registration Request";
    public $registrationRequestRejectedMessage = "Your registration request has been rejected. Please contact our customer support team if you have any questions or concerns.";
    // Payment Request
    public $paymentRequestApproval = "Approval of Payment Request";
    public $paymentRequestApprovedMessage = "Your payment request has been approved.";
    public $paymentRequestRejection = "Rejection of Payment Request";
    public $paymentRequestRejectedMessage = "Your payment request has been rejected.";
    public $paymentSettlementFor = "Payment Settlement for";
    public $paymentSettlementConfirmation = "Payment Settlement Confirmation";
    // Service Request
    public $serviceRequestApproval = "Approval of Service Request";
    public $serviceApprovalRequestApprovedMessage = "Your service approval request has been approved.";
    public $serviceRequestRejection = "Rejection of Service Request";
    public $serviceRequestApprovalRejectedMessage = "Your service approval request has been rejected. Please contact our customer support team if you have any questions or concerns.";
    // Booking Notifications
    public $newBookingNotification = "New Booking Notification";
    public $newBookingReceivedMessage = "We are pleased to inform you that you have received a new booking.";
    public $bookingStatusChange="Booking status change";
    public $bookingStatusUpdateMessage  ="Your Booking status has been";
    public $awaiting="Awaiting";
    public $confirmed="Confirmed";
    public $rescheduled="Reschedulled";
    public $cancelled="Cancelled";
    public $completed="Completed";
    public $started="Started";
    public $bookingEnded="Booking Ended";
    public $statusChangeBlockedMessage="You cannot alter the status that has already been marked as";
    public $bidRecevidedTitle="Received Bid";
    public $bidRecevidedMessage="You have received bid";


}
