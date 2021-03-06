<?php
namespace CB;

class Calendar
{
    public function getEvents($p)
    {
        $rez = array('success' => true, 'data' => array() );

        if (empty($p['start']) || empty($p['end'])) {
            return $rez;
        }

        $p['fl'] = 'id, template_id, name, date, date_end, category_id, status';
        if (!empty($p['path'])) {
            $rez['pathtext'] = Path::getPathText($p);
            $rez['folderProperties'] = Path::getPathProperties($p);
        }

        $pid = explode('/', @$p['path']);
        $pid = array_pop($pid);
        $pid = is_numeric($pid) ? $pid : Browser::getRootFolderId();
        if (empty($p['descendants'])) {
            $p['pid'] = $pid;
        } else {
            $p['pids'] = $pid;
        }
        $p['dateStart'] = $p['start'];//.'Z';
        unset($p['start']);
        $p['dateEnd'] = $p['end'];//substr($p['end'], 0, 10).'T23:59:59.999Z';
        unset($p['end']);

        $p['template_types'] = array('task');

        $s = new Search();
        $sr = $s->query($p);
        if (!empty($sr['data'])) {
            for ($i=0; $i < sizeof($sr['data']); $i++) {
                $d = $sr['data'][$i];
                $catIcon = '';
                if (!empty($d['category_id'])) {
                    $catIcon = Util\getThesauryIcon($d['category_id']);
                    if (!empty($catIcon)) {
                        $catIcon = ' cal-cat-'.$catIcon;
                    }
                }
                /* quick fix. Maybe add allday to solr */
                $res = DB\dbQuery(
                    'SELECT allday FROM tasks WHERE id = $1',
                    $d['id']
                ) or die(DB\dbQueryError());

                if ($r = $res->fetch_assoc()) {
                    $d['allday'] = ($r['allday'] == 1);
                }
                $res->close();
                /* end of quick fix. Maybe add allday to solr */
                @$rez['data'][] = array(
                    'id' => $d['id']
                    ,'ad' => $d['allday']
                    ,'template_id' => $d['template_id']
                    ,'cid' => 1 //$d['cid'] cid is calendar id
                    ,'title' => $d['name']
                    ,'start' => $d['date']
                    ,'category_id' => $d['category_id']
                    ,'end' => Util\coalesce($d['date_end'], $d['date'])
                    ,'status' => $d['status']
                );
            }
        }

        if (!empty($sr['facets'])) {
            $rez['facets'] = $sr['facets'];
        }

        return $rez;
    }
}
