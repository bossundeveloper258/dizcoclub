import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-payment-error',
  templateUrl: './payment-error.component.html',
  styleUrls: ['./payment-error.component.css']
})
export class PaymentErrorComponent implements OnInit {
  routeAsset = environment.assetsUrl;
  description!: string;
  constructor(
    private activatedRoute: ActivatedRoute,
  ) { 

    this.activatedRoute.queryParams
      .subscribe(params => {
        this.description = params.d;
      }
    );
  }

  ngOnInit(): void {
  }

}
