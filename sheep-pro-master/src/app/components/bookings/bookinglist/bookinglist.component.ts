import { Component, OnInit, TemplateRef, ViewChild, ElementRef, OnDestroy  } from '@angular/core';
import { BookingService, NotficationService,  UserService } from '../../../_services/index';
import { Subject } from 'rxjs/Rx';
import { ModalDirective } from 'ngx-bootstrap/ng2-bootstrap';
import { Router, ActivatedRoute, ParamMap } from '@angular/router';    
import { BsModalService } from 'ngx-bootstrap/modal';
import { BsModalRef } from 'ngx-bootstrap/modal/modal-options.class';
import { ConfirmationService } from '@jaspero/ng2-confirmations';
import { DataTableDirective } from 'angular-datatables';
import { BsDatepickerConfig } from 'ngx-bootstrap/datepicker';
import { DatePipe } from '@angular/common';
declare var jQuery: any;

@Component({
  selector: 'app-bookinglist',
  templateUrl: './bookinglist.component.html',
  styleUrls: ['./bookinglist.component.css']
})
export class BookinglistComponent implements OnInit {
	@ViewChild('childModal') childModal: ModalDirective;
	@ViewChild(DataTableDirective)
	datatableElement: DataTableDirective;
	 _session  = JSON.parse(localStorage.getItem('currentUser'));
	id:any;
	min: any;
	scanner: any;
	scanners:object;
  	max: any;
  	bsConfig = Object.assign({}, { containerClass: 'theme-blue' });

	public modalRef: BsModalRef;
	dtOptions: DataTables.Settings = {};
	bookings: any[] = [];
	selectedBooking:any;
	dtTrigger: any = new Subject();
  	constructor(private bookingService: BookingService,
  		private notficationService:NotficationService,
 		private modalService: BsModalService,
 		private _confirmation: ConfirmationService,
 		public datepipe: DatePipe,
 		private route: ActivatedRoute,
 		private userService: UserService) { }

  	ngOnInit() {

  		this.dtOptions = {
  			"ordering": true,
  			"order": [],
  			"scrollX": true
  		};

  		//Get Clients Here
	    this.bookingService.getAllBookings().subscribe(
	      	data => {
	        if(data.status) {
	        this.bookings = data.data;
        	this.dtTrigger.next();
	        }
	      },
	      error => {
	        this.notficationService.msg('error','Error!',error);
	    });
	    //End here

	    $.fn['dataTable'].ext.search.push((settings, data, dataIndex) => {
	    	if(this.min!= undefined && this.scanner != undefined){
	    		var start: any = new Date(this.min[0]); 
			    var end: any = new Date(this.min[1]); 
			    var parts =data[0].split('-');
			    var m = parts[1]-1;
				var d = parts[0];
				var y = parts[2];
				var current = new Date(y,m,d); 
				if 	(current>=start && current<=end){
			 		var str = data[4]; 
	    			var n = str.search(this.scanner);
	    			if(n<0){
	    				return  false;
	    			}else{
	    				return  true;
	    			}
			    }else{
			        return false;
			    }
	    	}
	    	else if(this.min!= undefined)
	    	{
	    		var start: any = new Date(this.min[0]); 
			    var end: any = new Date(this.min[1]); 
			    var parts =data[0].split('-');
			    var m = parts[1]-1;
				var d = parts[0];
				var y = parts[2];

				var current = new Date(y,m,d); 
				if 	(current>=start && current<=end){
			 		return true;
			    }else{
			        return false;
			    }
	    	}else if(this.scanner != undefined){
	    		var str = data[4]; 
    			var n = str.search(this.scanner);
    			if(n<0){
    				return  false;
    			}else{
    				return  true;
    			}
	    	}
		    return  true;
		});


		this.userService.activeUsers({'users':['Scanner']}).subscribe(
        data => {
            if(data.status) {
                this.scanners = data.Scanner;
                 setTimeout(() => {
                   jQuery('.scanner').selectpicker('refresh');
               }, 500);
            }
        },
        error => {
            this.notficationService.msg('error','Error!',error);
        });


		this.route.params.subscribe(params => {
            this.id = Number.parseInt(params['id']);;
            if(isNaN(this.id) != true) {
	            this.getView(this.id);
            }
        });
  	}

  	//On View Function 
  	onView(client: any) {
		this.modalRef = this.modalService.show(this.childModal);
		//Get Clients Here
	    this.bookingService.viewBooking(client.join_id).subscribe(
	      	data => {
	        if(data.status) {
	        	this.selectedBooking = data.data;
	        		var demo = data.data.unavailable_dates.split(',');
	        		if(demo!='')
	        		{
		        		for(var i = 0;i<demo.length;i++) { 
						   var parts =demo[i].split('-');
						   demo[i] = new Date(parts[2], parts[1], parts[0]); 
						} 
	        		}
	        	this.selectedBooking.unavailable_dates = demo;
	        }
	      },
	      error => {
	        this.notficationService.msg('error','Error!',error);
	    });
	    //End here
	} 

		//On View Function 
  	getView(client: any) {
		//Get Clients Here
	    this.bookingService.viewBooking(client).subscribe(
	      	data => {
	        if(data.status && data.data) {
	        	
	        	this.selectedBooking = data.data;
	        		var demo = data.data.unavailable_dates.split(',');
	        		if(demo!='')
	        		{
		        		for(var i = 0;i<demo.length;i++) { 
						   var parts =demo[i].split('-');
						   demo[i] = new Date(parts[2], parts[1], parts[0]); 
						  
						} 
	        		}
	        	this.selectedBooking.unavailable_dates = demo;
				this.modalRef = this.modalService.show(this.childModal);
	        }
	      },
	      error => {
	        this.notficationService.msg('error','Error!',error);
	    });
	    //End here
	}
filterById(): void {
    this.datatableElement.dtInstance.then((dtInstance: DataTables.Api) => {
      dtInstance.draw();
    });
  }
	 ngOnDestroy(): void {
    $.fn['dataTable'].ext.search.pop();
  }

  	onChange(event) {
  		var start =this.datepipe.transform(event[0], 'dd-MM-yyyy');
		var end =this.datepipe.transform(event[1], 'dd-MM-yyyy');
    	this.max = start+' - '+end;
	}

	onStatusChange(booking:any,status:string){
		if(status!=''){
			if(status=='scheduled'){
				var msg =  'Do you want to approve booking ?';
			} else if(status=='completed') {
				var msg = 'Do you want to complete booking ?';
			} else if (status=='canceled') {
				var msg = 'Do you want to cancel booking ?';
			}else{
				var msg = "Do you want to mark booking as pending";
			}
			this._confirmation.create('Confirm',msg).subscribe((ans:any) => {
				if(ans.resolved){
					booking.isDisable = true;
					booking.action ='waiting...';
					var previous_status = booking.status;
					booking.status = status;	
					this.bookingService.updateBookingStatus(booking.join_id,status).subscribe(data => {
						if(data.status){
							this.notficationService.msg('success','Success!',data.msg);
						} else {
							this.notficationService.msg('error','Error!',data.msg);
							booking.status = previous_status;
						}
						booking.isDisable = false;
						booking.action ='Action';
					})
				}
			})
		}
	}


	RegenrateBooking(booking:any){
		var msg = "Do you want to generate new events at google calendar ? ";
		this._confirmation.create('Confirm',msg).subscribe((ans:any) => {
			if(ans.resolved){
				var previous_status = booking.google_delete;
				booking.google_delete = 'Not';	
				booking.isDisable = true;
				booking.action ='waiting...';
				this.bookingService.regenrateBookings(booking.join_id).subscribe(data => {
					if(data.status){
						this.notficationService.msg('success','Success!',data.msg);
					} else {
						this.notficationService.msg('error','Error!',data.msg);
						booking.google_delete = previous_status;
					}
					booking.isDisable = false;
					booking.action ='Action';
				},(x)=>{
					this.notficationService.msg('error','Error!','Something went wrong');
					booking.google_delete = previous_status;
					booking.isDisable = false;
					booking.action ='Action';
				})
			}
		})
	}

}
