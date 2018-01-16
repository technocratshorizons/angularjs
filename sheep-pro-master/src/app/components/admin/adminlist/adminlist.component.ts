import { Component, OnInit, TemplateRef, ViewChild, ElementRef  } from '@angular/core';
import { UserService, NotficationService } from '../../../_services/index';
import { Subject } from 'rxjs/Rx';
import { ModalDirective } from 'ngx-bootstrap/ng2-bootstrap';
import { BsModalService } from 'ngx-bootstrap/modal';
import { BsModalRef } from 'ngx-bootstrap/modal/modal-options.class';
import { ConfirmationService } from '@jaspero/ng2-confirmations';
import { Router } from '@angular/router'
@Component({
  selector: 'app-adminlist',
  templateUrl: './adminlist.component.html',
  styleUrls: ['./adminlist.component.css']
})
export class AdminlistComponent implements OnInit {
	@ViewChild('childModal') childModal: ModalDirective;
	public modalRef: BsModalRef;
	dtOptions: DataTables.Settings = {};
	clients: any[] = [];
	dtTrigger: any = new Subject();
	selectedClient: any;
  	bsModalRef: BsModalRef;
  	_session = JSON.parse(localStorage.getItem('currentUser'));
 	constructor(private userService:UserService,
 		private notficationService:NotficationService,
 		private modalService: BsModalService,
 		private _confirmation: ConfirmationService,
 		private router:Router
 		) { }
  	ngOnInit() {
  		if(this._session.user_type!='Admin'){
  				this.router.navigate(['404']);
  			}
  		this.dtOptions = {
  			"ordering": true,
  			"order": [],
  			"scrollX": true
  		};

  		//Get Clients Here
	    this.userService.getAllUsers('Admin').subscribe(
	      	data => {
	        if(data.status) {
	        this.clients = data.data;
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
