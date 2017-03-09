<?php
/**
 * Core file.
 *
 * @author Vince Wooll <sales@jomres.net>
 *
 * @version Jomres 9.8.24
 *
 * @copyright	2005-2017 Vince Wooll
 * Jomres (tm) PHP, CSS & Javascript files are released under both MIT and GPL2 licenses. This means that you can choose the license that best suits your project, and use it accordingly
 **/

// ################################################################
defined('_JOMRES_INITCHECK') or die('');
// ################################################################

class j00005fake_webhook_calls
{
    public function __construct($componentArgs)
    {
    // Script designed to fire off webhook calls, regardless of the activity performed in Jomres. Used for web development.
    
    if (!defined("AJAXCALL") || !AJAXCALL) {
        
        $webhook_data = array();
        
        // The ids of various items in the Jomres tables that allow us to test functionality.
        
        $property_uid = 1;                          // A valid property uid
        $contract_uid = 41;                         // A contract uid that belongs to the property uid above
        $incorrect_property_uid = 99999999;         // A property that the manager does NOT have rights for
        $black_booking_id = 43;                     // A black booking that belongs to the property uid above
        $deleted_black_booking_id = 1000;           // A non-existant contract id to test that the webhook api feature correctly responds that the contract uid doesn't exist
        $cancelled_booking = 37;                    // A booking that has been cancelled
        $note_id = 46;                              // A note that has been added to a booking
        $deleted_note_id = 99999999;                // A non-existant note id to replicate a note that has been deleted
        $depositref = 'xyz123';                     // A made-up deposit reference
        $extras_uid = 2;                            // An optional extra's id
        $guest_uid = 13;                            // A guest's id
        $deleted_guest_uid = 99999999;              // A non-existant guest id to replicate a guest that has been deleted
        $guest_type_uid = 18;                       // A guest type id
        $deleted_guest_type_uid = 99999999;         // A non-existant guest type id to replicate a guest type that has been deleted
        $pending_invoice_uid = 24;                  // An invoice that is currently marked as pending
        $paid_invoice_uid = 25;                     // An invoice that is currently marked as paid
        $cancelled_invoice_uid = 28;                // An invoice that is currently marked as cancelled
        $nonexistant_invoice_uid = 99999999;        // An invoice id that does not exist
        
        $property_manager_assigned_to_uid = 2;      // A property that the manager is assigned to
        $property_nonexistant_uid = 10000;          // A property that does not exist
        $property_manager_not_assigned_to_uid = 7;  // A property that the manager is NOT assigned to
        
        $review_uid = 2;                            // A review's id. Should belong to the $property_uid also configured here
        $deleted_review_uid = 99999;                // A non-existant review id to replicate a review that has been deleted
        
        $room_uid = 54;                             // A rooms's id. Should belong to the $property_uid also configured here
        $deleted_room_uid = 99999;                  // A non-existant room id to replicate a room that has been deleted
        
        // The webhook events we will trigger. Comment/uncomment as needed
        /* $webhook_event = 'booking_modified';
        $webhook_data = array ( "property_uid" => $property_uid, "contract_uid" => $contract_uid );  */
        
        /* $webhook_event = 'booking_modified';
        $webhook_data = array ( "property_uid" => $incorrect_property_uid, "contract_uid" => $contract_uid );*/
        
        /*$webhook_event = 'blackbooking_added';
        $webhook_data = array ( "property_uid" => $property_uid, "contract_uid" => $black_booking_id );*/
        
        /*$webhook_event = 'blackbooking_deleted';
        $webhook_data = array ( "property_uid" => $property_uid, "contract_uid" => $deleted_black_booking_id );*/
        
        /* $webhook_event = 'booking_added';
        $webhook_data = array ( "property_uid" => $property_uid, "contract_uid" => $contract_uid ); */
        
        /* $webhook_event = 'booking_cancelled';
        $webhook_data = array ( "property_uid" => $property_uid, "contract_uid" => $cancelled_booking ); */
        
        /*$webhook_event = 'booking_note_deleted';
        $webhook_data = array ( "property_uid" => $property_uid, "note_uid" => $deleted_note_id ); */
        
        /*$webhook_event = 'booking_note_save';
        $webhook_data = array ( "property_uid" => $property_uid, "note_uid" => $note_id );*/
        
        /* $webhook_event = 'deposit_saved';
        $webhook_data = array ( "property_uid" => $property_uid, "contract_uid" => $contract_uid ); */
        
        /* $webhook_event = 'extra_deleted';
        $webhook_data = array ( "property_uid" => $property_uid, "extra_uid" => $extras_uid ); */
        
        /* $webhook_event = 'extra_saved';
        $webhook_data = array ( "property_uid" => $property_uid, "extra_uid" => $extras_uid ); */
        
        /* $webhook_event = 'guest_checkedin';
        $webhook_data = array ( "property_uid" => $property_uid, "contract_uid" => $contract_uid ); */
        
        /* $webhook_event = 'guest_checkedin_undone';
        $webhook_data = array ( "property_uid" => $property_uid, "contract_uid" => $contract_uid ); */
        
        /* $webhook_event = 'guest_checkedout';
        $webhook_data = array ( "property_uid" => $property_uid, "contract_uid" => $contract_uid ); */
        
        /* $webhook_event = 'guest_checkedout_undone';
        $webhook_data = array ( "property_uid" => $property_uid, "contract_uid" => $contract_uid ); */
        
        /* $webhook_event = 'guest_deleted';
        $webhook_data = array ( "property_uid" => $property_uid, "guest_uid" => $deleted_guest_uid ); */
        
        /* $webhook_event = 'guest_saved';
        $webhook_data = array ( "property_uid" => $property_uid, "guest_uid" => $guest_uid ); */
        
        /* $webhook_event = 'guest_type_deleted';
        $webhook_data = array ( "property_uid" => $property_uid, "guest_type_uid" => $deleted_guest_type_uid ); */
        
        /* $webhook_event = 'guest_type_saved';
        $webhook_data = array ( "property_uid" => $property_uid, "guest_type_uid" => $guest_type_uid ); */
        
        /* $webhook_event = 'invoice_cancelled';
        $webhook_data = array ( "property_uid" => $property_uid, "invoice_uid" => $nonexistant_invoice_uid ); */
        
        /* $webhook_event = 'invoice_cancelled';
        $webhook_data = array ( "property_uid" => $property_uid, "invoice_uid" => $pending_invoice_uid ); */
        
        /* $webhook_event = 'invoice_cancelled';
        $webhook_data = array ( "property_uid" => $property_uid, "invoice_uid" => $paid_invoice_uid ); */
        
        /* $webhook_event = 'invoice_saved';
        $webhook_data = array ( "property_uid" => $property_uid, "invoice_uid" => $pending_invoice_uid ); */
        
        /* $webhook_event = 'property_added';
        $webhook_data = array ( "property_uid" => $property_manager_assigned_to_uid); */
        
        /* $webhook_event = 'property_added';
        $webhook_data = array ( "property_uid" => $property_manager_not_assigned_to_uid ); */
        
        /* $webhook_event = 'property_added';
        $webhook_data = array ( "property_uid" => $property_nonexistant_uid );  */
        
        /* $webhook_event = 'property_deleted';
        $webhook_data = array ( "property_uid" => $property_nonexistant_uid );  */
        
        /* $webhook_event = 'property_published';
        $webhook_data = array ( "property_uid" => $property_manager_assigned_to_uid );  */
        
        /* $webhook_event = 'property_saved';
        $webhook_data = array ( "property_uid" => $property_manager_assigned_to_uid );  */ 
                
        /* $webhook_event = 'property_settings_updated';
        $webhook_data = array ( "property_uid" => $property_manager_assigned_to_uid );  */
        
        /* $webhook_event = 'property_unpublished';
        $webhook_data = array ( "property_uid" => $property_manager_assigned_to_uid ); */
                    
        /* $webhook_event = 'review_deleted';
        $webhook_data = array ( "property_uid" => $property_uid, "review_uid" => $deleted_review_uid ); */
        
        /* $webhook_event = 'review_published';
        $webhook_data = array ( "property_uid" => $property_uid, "review_uid" => $review_uid ); */
        
        /* $webhook_event = 'review_saved';
        $webhook_data = array ( "property_uid" => $property_uid, "review_uid" => $review_uid ); */
        
        /* $webhook_event = 'review_unpublished';
        $webhook_data = array ( "property_uid" => $property_uid, "review_uid" => $review_uid ); */
        
        /* $webhook_event = 'room_added';
        $webhook_data = array ( "property_uid" => $property_uid, "room_uid" => $room_uid ); */
        
        /* $webhook_event = 'room_deleted';
        $webhook_data = array ( "property_uid" => $property_uid, "room_uid" => $deleted_room_uid ); */
        
        /* $webhook_event = 'room_updated';
        $webhook_data = array ( "property_uid" => $property_uid, "room_uid" => $room_uid ); */
        
        /* $webhook_event = 'rooms_multiple_added';
        $webhook_data = array ( "property_uid" => $property_manager_assigned_to_uid ); */
        
        $webhook_event = 'tariffs_updated';
        $webhook_data = array ( "property_uid" => $property_manager_assigned_to_uid );
        
        if ( count($webhook_data) > 0 ) {
            $webhook_notification                               = new stdClass();
            $webhook_notification->webhook_event                = $webhook_event;
            $webhook_notification->data                         = new stdClass();
            foreach ( $webhook_data as $key=>$val ) {
                $webhook_notification->data->$key = $val;
                }
            add_webhook_notification($webhook_notification);
            }
        }
    }


    // This must be included in every Event/Mini-component
    public function getRetVals()
    {
        return null;
    }
}
