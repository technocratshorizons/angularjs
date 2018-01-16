import { Component, OnInit } from '@angular/core';
import { NotficationService, UserService } from '../../_services/index';
import { Router, ActivatedRoute, ParamMap } from '@angular/router';
import { NgForm } from '@angular/forms';

@Component({
  selector: 'app-profile',
  templateUrl: './profile.component.html',
  styleUrls: ['./profile.component.css']
})
export class ProfileComponent implements OnInit {
	isLoading: boolean = false;
	model: any = {};
    _session  = JSON.parse(localStorage.getItem('currentUser'));
  constructor(private userService: UserService, private router: Router, private notficationService: NotficationService) { }

  ngOnInit() {
  	this.userService.getUser(this._session.user_id)
	      .subscribe(data => {
	      		if(data.status){
	      			this.model = data.data;
	      			console.log(this.model);
	      		}
	      		else{
	      			this.router.navigate(['/scanner/list']);
	      		}
	      });
  }


  add_user(form: NgForm) {
		this.isLoading = true;
		this.userService.edit_profile(this.model)
		.subscribe(
			data => {
				this.isLoading = false;
				if(data.status) 
				{
					this.model.cpassword ="";
					this.model.npassword ="";
					this.model.rpassword ="";
					localStorage.removeItem('currentUser');
					localStorage.setItem('currentUser', JSON.stringify(data.data));
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
