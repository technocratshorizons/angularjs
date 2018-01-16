// customer.interface.ts
export interface Joining {
   	client_details: string; 
   	unavailable_dates: string; 
   	client_confirmation_email: any; 
   	client_confirmation_phone: any; 
   	notes: string;
    joinings_array: Joinings[]; 
}

export interface Joinings {
    // joining_title: string;  // required field
    number_of_sheep: string;
    scan_type: string;
    booking_date: Date;
    // time: string;
    scanner: string;
    room_in: Date[];
    introduced_days: string;
    joining_duration: string;
    room_free_days: string;
    ideal_days : string;
    date_to_scan: Date;
}