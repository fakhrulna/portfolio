import { NgModule } from "@angular/core";
import { Routes, RouterModule } from "@angular/router";
import { MainLayoutComponent } from "./main-layout/main-layout.component";
import { LoginLayoutComponent } from "./login-layout/login-layout.component";
import { AnalysisComponent } from "../analysis/analysis.component";
import { ApplicationComponent } from "../application/application.component";
import { ProductsComponent } from "../products/products.component";
import { AssestmentComponent } from "../assestment/assestment.component";
import { SettingsComponent } from "../settings/settings.component";
import { ApplicationDetailComponent } from "../application-detail/application-detail.component";
import { ProductDetailComponent } from "../product-detail/product-detail.component";
import { ProductAddComponent } from "../product-add/product-add.component";
import { ChangePasswordComponent } from "../change-password/change-password.component";
import { ApplicationFwdComponent } from "../application-fwd/application-fwd.component";
import { ApplicationFwdNewComponent } from "../application-fwd-new/application-fwd-new.component";
import { ExceptionalDetailComponent } from "../exceptional-detail/exceptional-detail.component";
import { GlobalSearchComponent } from "../global-search/global-search.component";

const routes: Routes = [
  // { path: '', redirectTo: '/home', pathMatch: 'full' },
  // { path: '**', redirectTo: '/home'},
  {
    path: "",
    component: MainLayoutComponent,
    children: [
      { path: "", loadChildren: "../home/home.module#HomeModule" },
      { path: "home", loadChildren: "../home/home.module#HomeModule" },
      { path: "application", component: ApplicationComponent },
      {
        path: "global-search/:src_id/:src_value",
        component: GlobalSearchComponent,
      },
      { path: "application-fwd", component: ApplicationFwdNewComponent },
      {
        path: "application-fwd/:status",
        component: ApplicationFwdNewComponent,
      },
      { path: "application-detail", component: ApplicationDetailComponent },
      {
        path: "exceptional-detail/:app_id",
        component: ExceptionalDetailComponent,
      },
      { path: "application-detail/:id", component: ApplicationDetailComponent },
      {
        path: "application-detail/:id/:status",
        component: ApplicationDetailComponent,
      },

      {
        path: "application-detail/:id/:status/:app_id/:app_code",
        component: ApplicationDetailComponent,
      },
      { path: "products", component: ProductsComponent },
      { path: "product-detail", component: ProductDetailComponent },
      { path: "product-detail/:id", component: ProductDetailComponent },
      { path: "product-add", component: ProductAddComponent },
      { path: "assestment", component: AssestmentComponent },
      { path: "setting", component: SettingsComponent },
      { path: "change-password", component: ChangePasswordComponent },
      // { path: '**', redirectTo: '/home'}
    ],
  },
  {
    path: "",
    component: LoginLayoutComponent,
    children: [
      { path: "login", loadChildren: "../login/login.module#LoginModule" },
      // { path: '**', redirectTo: '/login'}
    ],
  },
];

@NgModule({
  imports: [
    RouterModule.forChild(routes),
    RouterModule.forRoot(routes, { useHash: false }),
  ],
  exports: [RouterModule],
})
export class LayoutRoutingModule {}
