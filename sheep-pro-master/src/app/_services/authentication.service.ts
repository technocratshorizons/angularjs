import { Injectable } from '@angular/core';
import { Http, Headers, Response } from '@angular/http';
import { Observable } from 'rxjs/Observable';
import 'rxjs/add/operator/map'

@Injectable()
export class AuthenticationService {
    constructor(private http: Http) { }
	
	login(email: string, password: string) {
        var headers = new Headers();
        headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        return this.http.post('login/index', { email: email, password: password }, { headers: headers} )
            .map((response: Response) => {
                // login successful if there's a jwt token in the response
                let user = response.json();
                if(user.status) {
                    // alert('I am here');
                    // store user details and jwt token in local storage to keep user logged in between page refreshes
                    localStorage.setItem('currentUser', JSON.stringify(user.data));
                    // alert(user.data);
                }
                return user;
            });
    }


    forget(email: string) {
        var headers = new Headers();
		headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		return this.http.post('login/forget', { email: email }, { headers: headers} )
            .map((response: Response) => {
                // login successful if there's a jwt token in the response
                let user = response.json();
                if(user.status) {
				}
				return user;
            });
    }

    logout() {
        // remove user from local storage to log user out
        localStorage.removeItem('currentUser');
    }
}