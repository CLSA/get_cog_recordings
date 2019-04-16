Curl commands are stored in .curl files

To read all data in the recordings view run the following:
curl --config read_view.curl

To update the view with a new restriction date run the following:
curl --config update_view.curl --data "`sed -e 's/NEWDATE/2000-01-01/' update_view.json`"
(where 2000-01-01 is whatever date you want recordings to be on or after)
