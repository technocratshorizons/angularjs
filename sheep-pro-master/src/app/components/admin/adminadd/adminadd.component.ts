import { NgForm } from '@angular/forms';
import { Component, OnInit } from '@angular/core';
import { Http, Headers, Response } from '@angular/http';
import { Router, ActivatedRoute, ParamMap } from '@angular/router';
import { NotficationService, UserService } from '../../../_services/index';

@Component({
  selector: 'app-adminadd',
  templateUrl: './adminadd.component.html',
  styleUrls: ['./adminadd.component.css']
})
export class AdminaddComponent implements OnInit {
	isLoading: boolean = false;
	model: any = {};
	returnUrl: string;
	id;
	_session = JSON.parse(localStorage.getItem('currentUser'));
	constructor(private http: Http,
				private route: ActivatedRoute,
				private userService: UserService,
				private notficationService: NotficationService,
				private router: Router) { }

	ngOnInit() {
		if(this._session.user_type!='Admin'){
  				this.router.navigate(['404']);
  			}
  			
      this.route.params.subscribe(params => {
   			this.id = Number.parseInt(params['id'])
		});
      if(this.id)
      {
	      this.userService.getUser(this.id)
	      .subscribe(data => {
	      		if(data.status){
	      			this.model = data.data;
	      			console.log(this.model);
	      		}
	      		else{
	      			this.router.navigate(['/admin/list']);
	      		}
	      });
      }
	}

	add_user(form: NgForm) {
		this.isLoading = true;
 		this.model.user_type = 'Admin';
 		if(!this.id)
 		{
			this.userService.add_user(this.model)
			.subscribe(
				data => {
					this.isLoading = false;
					if(data.status) {
						form.resetForm({});
						this.back();
						this.notficationService.msg('success','Success!',data.msg);
					}
					else {
						this.notficationService.msg('error','Error!',data.msg);
					}
				},
				error => {
					this.notficationService.msg('error','Error!',error);
					this.isLoading = false;
				});
 		}
 		else
 		{
 			this.userService.edit_user(this.model)
			.subscribe(
				data => {
					this.isLoading = false;
					if(data.status) {
						this.back();
						this.notficationService.msg('success','Success!',data.msg);
					}
					else {
						this.notficationService.msg('error','Error!',data.msg);
					}
				},
				error => {
					this.notficationService.msg('error','Error!',error);
					this.isLoading = false;
				});
 		}
    }

    back() {
    	this.router.navigate(['/admin/list']);
    }
}
