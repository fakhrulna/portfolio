import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { PlatformLocation } from '@angular/common';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.css']
})
export class AppComponent implements OnInit {
  checklogin: any;
  path: any;

  constructor(
    private router: Router,
    private platformLocation: PlatformLocation
  ){
    
  }

  checkLogin() {
    this.checklogin = localStorage.getItem( 'access_token' );
    console.log('checklogin : ' + this.checklogin);
    
    if (this.checklogin === null) {
      this.router.navigate(['/login']);
    } else {
      this.path = (this.platformLocation as any).location.pathname;
      if (this.path === '/login') {
        this.router.navigate(['/home']);
      }
    }
  }

  ngOnInit() {
    this.checkLogin();
  }

}
