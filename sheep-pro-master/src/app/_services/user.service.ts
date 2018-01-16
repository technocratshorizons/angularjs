import { Injectable } from '@angular/core';
import { Http, Response, Headers } from '@angular/http';
import 'rxjs/add/operator/map';

@Injectable()
export class UserService {
  constructor (private http: Http ) {}
  
  //Get Users Function start here
  getUsers(Role:string) {
    var headers = new Headers();
    headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    return this.http.get(`users/getusers/`+Role,{headers:headers})
    .map((res:Response) => res.json());
  }

  //Get all Users Start here
  getAllUsers(Role:string) {
    var headers = new Headers();
    headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    return this.http.get(`users/get_all_users/`+Role,{headers:headers})
    .map((res:Response) => res.json());
  }

  //Get Single User
  getUser(Id:number) {
    var headers = new Headers();
    headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    return this.http.get(`users/getuser/`+Id,{headers:headers})
    .map((res:Response) => res.json());
  }

  //Add user function 
  add_user(FormData: string) {
		var headers = new Headers();
		headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		return this.http.post('users/add', FormData, { headers: headers} )
            .map((response: Response) => {
				return response.json()
			});
    }

  //Add user function 
  edit_user(FormData: string) {
    var headers = new Headers();
    headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    return this.http.post('users/edit', FormData, { headers: headers} )
            .map((response: Response) => {
        return response.json()
      });
    }

     //Add user function 
    edit_profile(FormData: string) {
    var headers = new Headers();
    headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    return this.http.post('users/edit_profile', FormData, { headers: headers} )
            .map((response: Response) => {
        return response.json()
      });
    }

    //Deactivate User here
    deActivateUser(FormData: object) {
    var headers = new Headers();
    headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    return this.http.post('users/deactivate', FormData, { headers: headers} )
            .map((response: Response) => {
        return response.json()
      });
    }


    //Deactivate User here
    activateUser(FormData: object) {
    var headers = new Headers();
    headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    return this.http.post('users/activate', FormData, { headers: headers} )
            .map((response: Response) => {
        return response.json()
      });
    }


    //User by Roles Multiple
    activeUsers(FormData: object) {
    var headers = new Headers();
    headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    return this.http.post('users/multiple_users', FormData, { headers: headers} )
            .map((response: Response) => {
        return response.json()
      });
    } 


    getAllUsersCount() {
    var headers = new Headers();
    headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    return this.http.get('users/all_users_count')
            .map((response: Response) => {
        return response.json()
      });
    }

    exportTables(type:string=null) {
    var headers = new Headers();
    headers.append('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    return this.http.get('users/exportData/'+type)
            .map((response: Response) => {
        return response.json()
      });
    }


    
}