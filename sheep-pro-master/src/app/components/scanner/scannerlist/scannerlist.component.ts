import { Component, OnInit, TemplateRef, ViewChild, ElementRef  } from '@angular/core';
import { UserService, NotficationService } from '../../../_services/index';
import { Subject } from 'rxjs/Rx';
import { ModalDirective } from 'ngx-bootstrap/ng2-bootstrap';
import { BsModalService } from 'ngx-bootstrap/modal';
import { BsModalRef } from 'ngx-bootstrap/modal/modal-options.class';
import { ConfirmationService } from '@jaspero/ng2-confirmations';
import { Router } from '@angular/router';
import { ExportToCsv } from 'export-to-csv';

@Component({
  selector: 'app-scannerlist',
  templateUrl: './scannerlist.component.html',
  styleUrls: ['./scannerlist.component.css']
})
export class ScannerlistComponent implements OnInit {
	@ViewChild('childModal') childModal: ModalDirective;
	public modalRef: BsModalRef;
	dtOptions: any = {};
	clients: any[] = [];
	exportClients: Array<any> = []
	dtTrigger: any = new Subject();
	selectedClient: any;
  	bsModalRef: BsModalRef;
  	_session = JSON.parse(localStorage.getItem('currentUser'));
 	constructor(private userService:UserService,
 		private notficationService:NotficationService,
 		private modalService: BsModalService,
 		private _confirmation: ConfirmationService,
 		private router: Router
 		) { }
  	ngOnInit() {
  		let self = this;
  		if(this._session.user_type!='Admin'){
  				this.router.navigate(['404']);
  			}
  		this.dtTrigger.next();
  		this.dtOptions = {
  			"ordering": true,
  			"order": [],
  			"scrollX": true,
  			// Declare the use of the extension in the dom parameter
		    // dom: 'Bfrtip',
		      // Configure the buttons
		    // buttons: [
		    //     'excel',
		    //     {
		    //       	text: 'Export Data',
		    //       	key: '1',
		    //       	action: function (e, dt, node, config) {
						// var options = { 
						//     showLabels: true,
						//     useKeysAsHeaders: true
						// };
						 
						// const exportToCsv = new ExportToCsv(options);
						// exportToCsv.generateCsv(self.exportClients);
		    //       	}
		    //     }
		    // ]
  		};

  		//Get Clients Here
	    this.userService.getAllUsers('Scanner').subscribe(
	      	data => {
	        if(data.status && data.data) {
		        // if(data.data.length>0){
		        // 	data.data.forEach(i=>{ 
          //               this.exportClients.push({
          //               	'First Name':  i.first_name,
          //               	'Last Name' : i.last_name,
          //               	'Company': i.company,
          //               	'Email':i.email,
          //               	'secondary email':i.secondary_email,
          //               	'mobile phone':'+61'+i.mobile_phone,
          //               	'landline phone':i.landline_phone,
          //               	'address 1':i.address_1,
          //               	'address 2':i.address_2,
          //               	'suburb':i.suburb,
          //               	'state':i.state,
          //               	'notes':i.notes,
          //               	'status':(i.status=='1')?'Deactivated':'Activated',
          //               	'created Date':i.created,
          //               	'modified Date':i.modified
          //               });
          //           });
		        // }

		        this.clients = data.data;





	        	this.dtTrigger.next();
	        }
	        else{
	        	this.clients = [];
	        	this.dtTrigger.next();
	        }
	      },
	      error => {
	        this.notficationService.msg('error','Error!',error);
	    });
	    //End here
  	}

  	//On View Function 
  	onView(client: any) {
	  this.selectedClient = client;
	  this.selectedClient.created = this.selectedClient.created.replace(/-/g, "/");
	  this.modalRef = this.modalService.show(this.childModal);
	}

	deactivate(obj)
	{
		console.log(event);
		this._confirmation.create('Confirmation', 'Do you want to deactivate '+obj.user_type+' "'+obj.first_name+' '+obj.last_name+'"')
        .subscribe((ans: any) => {
        	if(ans.resolved){
        		this.userService.deActivateUser({'user_id':obj.user_id,'user_type':obj.user_type}).subscribe(
		      	data => {
		        if(data.status) {
		        		this.notficationService.msg('success','Success!',data.msg);
		        		obj.status = '1';
		        	} else {
		        		this.notficationService.msg('error','Error!',data.msg);
		        	}
		      	},
		      	error => {
		        	this.notficationService.msg('error','Error!','Something went wrong');
		    	});
        	}
        });
	}

	activate(obj)
	{
		this._confirmation.create('Confirmation', 'Do you want to activate '+obj.user_type+' "'+obj.first_name+' '+obj.last_name+'"')
        .subscribe((ans: any) => {
        	if(ans.resolved){
        		this.userService.activateUser({'user_id':obj.user_id,'user_type':obj.user_type}).subscribe(
		      	data => {
		        if(data.status) {
		        		this.notficationService.msg('success','Success!',data.msg);
		        		obj.status = '0';
		        	} else {
		        		this.notficationService.msg('error','Error!',data.msg);
		        	}
		      	},
		      	error => {
		        	this.notficationService.msg('error','Error!','Something went wrong');
		    	});
        	}
        });
	}
}
