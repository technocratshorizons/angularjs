import { Injectable } from '@angular/core';
import { Http, Response, Headers } from '@angular/http';
import { Joining } from '../joining.interface';
import 'rxjs/add/operator/map';

@Injectable()
export class BookingService {
  constructor (private http: Http ) {}
  
  //Add Booking function 
  addBooking(FormData: Joining) {
  		console.log(FormData);
  		// return  true;
		var headers = new Headers();
		headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		return this.http.post('booking/add', FormData, { headers: headers} )
            .map((response: Response) => {
				return response.json()
			});
    } 


    //Edit Booking function 
  	editBooking(FormData: string) {
		var headers = new Headers();
		headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		return this.http.post('booking/edit', FormData, { headers: headers} )
            .map((response: Response) => {
				return response.json()
			});
    } 

    //Get All function 
  	getAllBookings() {
		var headers = new Headers();
		headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		return this.http.get('booking/all_bookings', { headers: headers} )
            .map((response: Response) => {
				return response.json()
			});
    }    


    currentBooking(FormData: object){
		var headers = new Headers();
		headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		return this.http.post('booking/bookinginfo', FormData, { headers: headers} )
            .map((response: Response) => {
				return response.json()
			});
    }

    viewBooking(id) {
		var headers = new Headers();
		headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		return this.http.get('booking/view_bookings/'+id, { headers: headers} )
            .map((response: Response) => {
				return response.json()
			});
    }  

    updateBookingStatus(id:number,status:string) {
		var headers = new Headers();
		headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		return this.http.get('booking/change_status/'+id+'/'+status, { headers: headers} )
            .map((response: Response) => {
				return response.json()
			});
    }


    regenrateBookings(id:number) {
     		// return  true;
		var headers = new Headers();
		headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		return this.http.post('booking/regenrateBooking', {'Booking_Id':id }, { headers: headers} )
            .map((response: Response) => {
				return response.json()
		});
    } 
}