import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { ApiService } from '../api.service';

@Component({
  selector: 'app-product-detail',
  templateUrl: './product-detail.component.html',
  styleUrls: ['./product-detail.component.css']
})
export class ProductDetailComponent implements OnInit {

  sid: any
  productID: any
  checkID: any
  productDetail: any
  status: any

  constructor(
    private route: ActivatedRoute,
    private api: ApiService,
  ) { }

  ngOnInit() {
    this.sid = this.route.paramMap.source;
    if (this.sid) {
      this.checkID = this.sid.value.id
      this.productID = this.checkID
    } 
    
    if (!this.checkID) {
      this.productID = this.route.snapshot.queryParamMap.get('id')
    }
    console.log('ID : '+ this.productID)

    let artPayload = {
      id: this.productID      
    }
    this.getDetails(artPayload);

  }

  getDetails(payload) {

    this.api.getProductDetails(payload)
        .subscribe((data: any) => {
          this.productDetail = data.data[0];
          this.status = data.success;
          },
            err => console.error(err),
              () => console.log('productDetail (done)'));
        }

}
