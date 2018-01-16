import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';

@Component({
 selector: 'appheader',
 templateUrl: './appheader.component.html',
 styleUrls: ['./appheader.component.css']
})
export class AppheaderComponent implements OnInit {

    returnUrl: string;
    _session  = JSON.parse(localStorage.getItem('currentUser'));
    
    constructor(private route: ActivatedRoute,
       private router: Router) { }
    
    ngOnInit() {
        this.returnUrl = this.route.snapshot.queryParams['returnUrl'] || '/login/';
    }
    
    logout() {
        localStorage.removeItem('currentUser');
        this.router.navigate([this.returnUrl]);
   }
}