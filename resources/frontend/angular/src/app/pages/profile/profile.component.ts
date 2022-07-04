import { Component, OnInit } from '@angular/core';
import { StorageService } from 'src/app/core/services/storage.service';
import { UserModel } from 'src/app/shared/models/auth.model';
import { environment } from 'src/environments/environment';

@Component({
  selector: 'app-profile',
  templateUrl: './profile.component.html',
  styleUrls: ['./profile.component.css']
})
export class ProfileComponent implements OnInit {

  routeAsset = environment.assetsUrl;
  user?: UserModel;
  constructor(
    private storageService: StorageService,
  ) {
    this.user = this.storageService.getUser();
   }

  ngOnInit(): void {
  }

}
