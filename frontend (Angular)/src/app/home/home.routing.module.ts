import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
// import { TranslateLoader, TranslateModule } from '@ngx-translate/core';
// import { TranslateHttpLoader } from '@ngx-translate/http-loader';

import { HomeComponent } from './home.component';

const routes: Routes =[
    { path: '', component: HomeComponent },
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule]
})

export class HomeRoutingModule { }