// Generated by CoffeeScript 1.6.3
(function() {
  var Application, Job, JobView, JobsCollection, JobsView, Task, TaskCollection, TaskView, TasksView, baseUrl, _ref, _ref1, _ref2, _ref3, _ref4, _ref5, _ref6, _ref7, _ref8,
    __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  baseUrl = "http://ping.me/app_dev.php";

  $(function() {
    var application;
    return application = new Application(new Date);
  });

  Application = (function(_super) {
    __extends(Application, _super);

    function Application() {
      _ref = Application.__super__.constructor.apply(this, arguments);
      return _ref;
    }

    Application.prototype.currentDate = null;

    Application.prototype.el = "#container";

    Application.prototype.events = {
      'click div.nextDay a': 'loadNextDay'
    };

    Application.prototype.loadNextDay = function(event) {
      event.preventDefault();
      event.stopPropagation();
      this.currentDate = moment(moment(this.currentDate)).add('days', -1).toDate();
      return new JobsView(this.currentDate);
    };

    Application.prototype.initialize = function(date) {
      this.currentDate = date;
      return new JobsView(this.currentDate);
    };

    return Application;

  })(Backbone.View);

  Job = (function(_super) {
    __extends(Job, _super);

    function Job() {
      _ref1 = Job.__super__.constructor.apply(this, arguments);
      return _ref1;
    }

    Job.prototype.url = function() {
      return baseUrl + "/api/v1/job/" + this.id + ".json";
    };

    Job.prototype.defaults = {
      id: null,
      name: null,
      dateCreation: null,
      lastStatus: null,
      globalStatus: null
    };

    return Job;

  })(Backbone.Model);

  Task = (function(_super) {
    __extends(Task, _super);

    function Task() {
      _ref2 = Task.__super__.constructor.apply(this, arguments);
      return _ref2;
    }

    Task.prototype.url = function() {
      return baseUrl + "/api/v1/task/" + this.id + ".json";
    };

    Task.prototype.defaults = {
      id: null,
      name: null,
      dateCreation: null,
      dateUpdate: null,
      startedBy: null,
      status: null,
      message: null
    };

    return Task;

  })(Backbone.Model);

  JobsCollection = (function(_super) {
    __extends(JobsCollection, _super);

    function JobsCollection() {
      _ref3 = JobsCollection.__super__.constructor.apply(this, arguments);
      return _ref3;
    }

    JobsCollection.prototype.model = Job;

    JobsCollection.prototype.date = null;

    JobsCollection.prototype.initialize = function(date) {
      return this.date = date;
    };

    JobsCollection.prototype.url = function() {
      if (this.date !== null) {
        return baseUrl + "/api/v1/jobs/" + this.date.getFullYear() + "/" + (this.date.getMonth() + 1) + "/" + this.date.getDate() + "/list.json";
      } else {
        throw "The date has to be defined";
      }
    };

    return JobsCollection;

  })(Backbone.Collection);

  TaskCollection = (function(_super) {
    __extends(TaskCollection, _super);

    function TaskCollection() {
      _ref4 = TaskCollection.__super__.constructor.apply(this, arguments);
      return _ref4;
    }

    TaskCollection.prototype.model = Task;

    TaskCollection.prototype.id = null;

    TaskCollection.prototype.initialize = function(id) {
      return this.id = id;
    };

    TaskCollection.prototype.url = function() {
      if (this.id) {
        return baseUrl + "/api/v1/job/tasks/" + this.id + ".json";
      } else {
        throw "The ID has to be defined";
      }
    };

    return TaskCollection;

  })(Backbone.Collection);

  TaskView = (function(_super) {
    __extends(TaskView, _super);

    function TaskView() {
      _ref5 = TaskView.__super__.constructor.apply(this, arguments);
      return _ref5;
    }

    TaskView.prototype.tagName = "li";

    TaskView.prototype.events = {
      'click .message': 'showMessage'
    };

    TaskView.prototype.initialize = function() {
      return this.model.bind("sync", this.updateRender);
    };

    TaskView.prototype.render = function() {
      var tmpl;
      tmpl = _.template($("#taskTemplate").html());
      this.$el.attr('id', 'task-' + this.model.toJSON().id).html(tmpl(this.model.toJSON()));
      return this;
    };

    TaskView.prototype.updateRender = function() {
      var tmpl;
      tmpl = _.template($("#taskTemplate").html());
      return $("#task-" + this.id).html(tmpl(this.toJSON()));
    };

    TaskView.prototype.showMessage = function() {
      return alert(this.model.toJSON().message);
    };

    return TaskView;

  })(Backbone.View);

  TasksView = (function(_super) {
    __extends(TasksView, _super);

    function TasksView() {
      _ref6 = TasksView.__super__.constructor.apply(this, arguments);
      return _ref6;
    }

    TasksView.prototype.id = null;

    TasksView.prototype.setId = function(id) {
      this.id = id;
      this.collection = new TaskCollection(id);
      this.collection.bind("add", this.renderTask, this);
      return this.collection.fetch().render;
    };

    TasksView.prototype.render = function() {
      var tmpl;
      tmpl = _.template($("#tasksTemplate").html());
      return tmpl({
        "id": this.id
      });
    };

    TasksView.prototype.renderTask = function(task) {
      var taskView;
      this.collection.add(task);
      taskView = new TaskView({
        model: task
      });
      return $("#tasks-" + this.id).append(taskView.render().el);
    };

    TasksView.prototype.update = function() {
      return this.collection.each(function(task) {
        return task.fetch();
      });
    };

    return TasksView;

  })(Backbone.View);

  JobView = (function(_super) {
    __extends(JobView, _super);

    function JobView() {
      _ref7 = JobView.__super__.constructor.apply(this, arguments);
      return _ref7;
    }

    JobView.prototype.tagName = "li";

    JobView.prototype.tasksView = null;

    JobView.prototype.events = {
      'click .infos': 'afficherTasks',
      'click .restartJob': 'restartJob',
      'click .abandonJob': 'abandonJob'
    };

    JobView.prototype.render = function() {
      var tmpl;
      tmpl = _.template($("#jobTemplate").html());
      this.$el.attr('id', 'jobs-' + this.model.toJSON().id).html(tmpl(this.model.toJSON()));
      return this;
    };

    JobView.prototype.updateRender = function() {
      var tmpl;
      tmpl = _.template($("#jobTemplate").html());
      return $("#jobs-" + this.model.id).html(tmpl(this.model.toJSON()));
    };

    JobView.prototype.afficherTasks = function(event) {
      var id;
      if (this.tasksView == null) {
        id = $(event.currentTarget).closest("div.infos").data("id");
        this.tasksView = new TasksView;
        this.tasksView.setId(id);
        this.$el.after(this.tasksView.render());
        return this.$el.toggleClass("open");
      } else {
        this.$el.next("ul.tasks").remove();
        this.tasksView = null;
        return this.$el.toggleClass("open");
      }
    };

    JobView.prototype.restartJob = function() {
      $.get(baseUrl + "/api/v1/jobs/restart/" + this.model.toJSON().id);
      this.model.set("globalStatus", 0);
      this.updateRender();
      if (this.tasksView !== null) {
        return this.tasksView.update();
      }
    };

    JobView.prototype.abandonJob = function() {
      $.get(baseUrl + "/api/v1/jobs/abandon/" + this.model.toJSON().id);
      this.model.set("globalStatus", 4);
      this.updateRender();
      if (this.tasksView !== null) {
        return this.tasksView.update();
      }
    };

    return JobView;

  })(Backbone.View);

  JobsView = (function(_super) {
    __extends(JobsView, _super);

    function JobsView() {
      _ref8 = JobsView.__super__.constructor.apply(this, arguments);
      return _ref8;
    }

    JobsView.prototype.date = null;

    JobsView.prototype.initialize = function(date) {
      var _this = this;
      this.date = date;
      this.collection = new JobsCollection(date);
      this.collection.bind("add", this.renderJob, this);
      this.collection.fetch({
        success: function(collection, result) {
          if (collection.length === 0) {
            return $("#" + _this.cid + " ul.jobs").append($("#jobTemplateEmpty").html());
          }
        }
      }, this);
      return this.render();
    };

    JobsView.prototype.render = function() {
      var data, tmpl;
      data = {
        "date": this.date,
        "cid": this.cid
      };
      tmpl = _.template($("#dayTemplate").html());
      return $("#container div.nextDay").before(tmpl(data));
    };

    JobsView.prototype.renderJob = function(job) {
      var jobView;
      this.collection.add(job);
      jobView = new JobView({
        model: job
      });
      return $("#" + this.cid + " ul.jobs").append(jobView.render().el);
    };

    return JobsView;

  })(Backbone.View);

}).call(this);
