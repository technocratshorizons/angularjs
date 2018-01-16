import { Injectable } from '@angular/core';
import { SimpleNotificationsModule, NotificationsService } from 'angular2-notifications';
@Injectable()
export class NotficationService{
	constructor(private _service: NotificationsService) { }
    msg(status,title,message){
    	if(status=='success'){
	    	this._service.success(title, message, {
		      timeOut: 3000,
		      showProgressBar: true,
		      pauseOnHover: true,
		      clickToClose: true
		    });
    	}else if(status=='error'){
    		this._service.error(title, message, {
		      timeOut: 3000,
		      showProgressBar: false,
		      pauseOnHover: true,
		      clickToClose: true
		    });
    	}else if(status=='info'){
    		this._service.info(title, message, {
		      timeOut: 3000,
		      showProgressBar: true,
		      pauseOnHover: true,
		      clickToClose: true
		    });
    	}else if(status=='warn'){
    		this._service.info(title, message, {
		      timeOut: 3000,
		      showProgressBar: true,
		      pauseOnHover: true,
		      clickToClose: true
		    });
    	}
    }
}