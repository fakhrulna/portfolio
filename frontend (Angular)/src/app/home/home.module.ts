// import { BrowserModule } from '@angular/platform-browser';
import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
// import { NgbModule } from '@ng-bootstrap/ng-bootstrap';
import { RouterModule } from '@angular/router';

import { HomeComponent } from './home.component';

// import { SectionsModule } from '../sections/sections.module';

// import { HttpClient } from '@angular/common/http';
// import { HttpClientModule } from '@angular/common/http';
import { HomeRoutingModule } from './home.routing.module';

@NgModule({
    imports: [
        CommonModule,
        // BrowserModule,
        FormsModule,
        RouterModule,
        HomeRoutingModule,
    ],
    declarations: [ 
        HomeComponent
    ],
    exports : [ 
    ],
    providers: []
})
export class HomeModule { }