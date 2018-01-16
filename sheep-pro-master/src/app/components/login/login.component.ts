import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';

import { AlertService,AuthenticationService } from '../../_services/index';

@Component({
  selector: 'app-login',
  templateUrl: './login.component.html',
  styleUrls: ['./login.component.css']
})
export class LoginComponent implements OnInit {
	
	loginError: boolean;
	loginErrorMessage: string;
	model: any = {};
    loading = false;
    returnUrl: string;
	isLoading: boolean = false;

	constructor(
        private route: ActivatedRoute,
        private router: Router,
        private authenticationService: AuthenticationService,
        private alertService: AlertService) { }

    ngOnInit() {

    	// If user already login
        if (localStorage.getItem('currentUser')) {
            this.router.navigate(['/dashboard']);
       }
                // get return url from route parameters or default to '/'
        this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/dashboard/';
    }

    login() {
		this.isLoading =true;
        this.authenticationService.login(this.model.email, this.model.password)
		.subscribe(
			data => {
				this.isLoading =false;
				if(data.status) {
					this.router.navigate([this.returnUrl]);
				}
				else {
					this.loginError = true;
					this.loginErrorMessage = data.msg;
				}
			},
			error => {
				this.alertService.error(error);
				this.loading = false;
			});
    }
}