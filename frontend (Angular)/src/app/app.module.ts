import { BrowserModule } from "@angular/platform-browser";
import { NgModule } from "@angular/core";
import { ChartsModule } from "ng2-charts";
import { NgbModule } from "@ng-bootstrap/ng-bootstrap";

// import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from "./app.component";
import { RouterModule } from "@angular/router";
import { LayoutModule } from "./layout/layout.module";
import { FormsModule, ReactiveFormsModule } from "@angular/forms";
import { HttpClientModule, HTTP_INTERCEPTORS } from "@angular/common/http";
import { ApplicationComponent } from "./application/application.component";
import { ProductsComponent } from "./products/products.component";
import { AssestmentComponent } from "./assestment/assestment.component";
import { SettingsComponent } from "./settings/settings.component";
import { ApplicationDetailComponent } from "./application-detail/application-detail.component";
import { ProductDetailComponent } from "./product-detail/product-detail.component";
import { ProductAddComponent } from "./product-add/product-add.component";
import { DatePipe } from "@angular/common";
import { HttpConfigInterceptor } from "./httpconfig.interceptor";
import { ChangePasswordComponent } from "./change-password/change-password.component";
import { ApplicationFwdComponent } from "./application-fwd/application-fwd.component";
import { ApplicationFwdNewComponent } from "./application-fwd-new/application-fwd-new.component";
import { ExceptionalDetailComponent } from "./exceptional-detail/exceptional-detail.component";
import { GlobalSearchComponent } from "./global-search/global-search.component";

@NgModule({
  declarations: [
    AppComponent,
    ApplicationComponent,
    ApplicationFwdComponent,
    ApplicationFwdNewComponent,
    ProductsComponent,
    ProductDetailComponent,
    AssestmentComponent,
    SettingsComponent,
    ApplicationDetailComponent,
    ProductDetailComponent,
    ProductAddComponent,
    ChangePasswordComponent,
    ExceptionalDetailComponent,
    GlobalSearchComponent,
  ],
  imports: [
    BrowserModule,
    NgbModule,
    FormsModule,
    ReactiveFormsModule,
    HttpClientModule,
    ChartsModule,
    RouterModule.forRoot([]),
    LayoutModule,
  ],
  providers: [
    DatePipe,
    {
      provide: HTTP_INTERCEPTORS,
      useClass: HttpConfigInterceptor,
      multi: true,
    },
  ],
  bootstrap: [AppComponent],
})
export class AppModule {}
