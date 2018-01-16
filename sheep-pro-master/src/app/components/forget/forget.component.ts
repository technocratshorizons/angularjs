import { Component, OnInit } from '@angular/core';
import { NgForm } from '@angular/forms';
import { Router, ActivatedRoute } from '@angular/router';
import { AlertService,AuthenticationService } from '../../_services/index';

@Component({
  selector: 'app-forget',
  templateUrl: './forget.component.html',
  styleUrls: ['./forget.component.css']
})
export class ForgetComponent implements OnInit {
	loginError: boolean;
	loginErrorMessage: string;
	loginSuccess : boolean;
	loginSuccessMessage : string;
	model: any = {};
    loading = false;
    returnUrl: string;
	isLoading: boolean = false;
  constructor( private route: ActivatedRoute,
        private router: Router,
        private authenticationService: AuthenticationService,
        private alertService: AlertService) { }

  ngOnInit() {
  		// If user already login
        if (localStorage.getItem('currentUser')) {
            this.router.navigate(['/dashboard']);
       }
  }

  forget(form: NgForm) {
		this.isLoading =true;
        this.authenticationService.forget(this.model.email)
		.subscribe(
			data => {
				this.isLoading =false;
				if(data.status) {
					this.loginError = false;
					this.loginSuccess = true;
					this.loginSuccessMessage = data.msg;
					form.resetForm({});
				}
				else {
					this.loginError = true;
					this.loginErrorMessage = data.msg;
					this.loginSuccess = false;
				}
			},
			error => {
				this.alertService.error(error);
				this.loading = false;
			});
    }

}
